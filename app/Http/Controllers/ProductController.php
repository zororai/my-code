<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductSale;
use App\ProductSaleItem;
use App\StockMovement;
use App\SchoolIncome;
use App\CashBookEntry;
use App\ProductCategory;
use App\UniformCollection;
use App\Student;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->stock_status === 'low') {
            $query->whereRaw('quantity <= min_stock_level');
        } elseif ($request->stock_status === 'out') {
            $query->where('quantity', 0);
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Product::distinct()->whereNotNull('category')->pluck('category');

        return view('backend.finance.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::where('is_active', true)->orderBy('name')->pluck('name');
        return view('backend.finance.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $barcode = Product::generateBarcode();
        $sku = Product::generateSKU($request->name);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'sku' => $sku,
            'barcode' => $barcode,
            'description' => $request->description,
            'category' => $request->category,
            'image' => $imagePath,
            'price' => $request->price ?? 0,
            'cost_price' => $request->cost_price ?? 0,
            'quantity' => $request->quantity ?? 0,
            'min_stock_level' => $request->min_stock_level ?? 5,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        if ($product->quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->quantity,
                'stock_before' => 0,
                'stock_after' => $product->quantity,
                'reason' => 'Initial stock',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('finance.products')
            ->with('success', 'Product created successfully. Barcode: ' . $barcode);
    }

    public function show($id)
    {
        $product = Product::with(['stockMovements' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(20);
        }, 'saleItems.sale'])->findOrFail($id);

        return view('backend.finance.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Product::distinct()->whereNotNull('category')->pluck('category');
        return view('backend.finance.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'image' => $imagePath,
            'price' => $request->price ?? 0,
            'cost_price' => $request->cost_price ?? 0,
            'min_stock_level' => $request->min_stock_level ?? 5,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('finance.products')
            ->with('success', 'Product updated successfully.');
    }

    public function adjustStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string',
        ]);

        $stockBefore = $product->quantity;

        if ($request->adjustment_type === 'add') {
            $product->quantity += $request->quantity;
            $movementType = 'in';
        } elseif ($request->adjustment_type === 'remove') {
            $product->quantity = max(0, $product->quantity - $request->quantity);
            $movementType = 'out';
        } else {
            $product->quantity = $request->quantity;
            $movementType = 'adjustment';
        }

        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $movementType,
            'quantity' => $request->quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->quantity,
            'reason' => $request->reason ?? 'Manual adjustment',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Stock adjusted successfully. New quantity: ' . $product->quantity);
    }

    public function pos()
    {
        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('backend.finance.products.pos', compact('products'));
    }

    public function findByBarcode(Request $request)
    {
        $product = Product::where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        if ($product->quantity <= 0) {
            return response()->json(['success' => false, 'message' => 'Product out of stock']);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => floatval($product->price),
                'quantity' => $product->quantity,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
            ]
        ]);
    }

    public function processSale(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'amount_paid' => 'required|numeric|min:0',
                'payment_method' => 'required|string',
            ]);

            $totalAmount = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . $product->name
                ], 400);
            }

            $itemTotal = $product->price * $item['quantity'];
            $totalAmount += $itemTotal;

            $itemsData[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $itemTotal,
            ];
        }

        if ($request->amount_paid < $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment amount'
            ], 400);
        }

        $sale = ProductSale::create([
            'sale_number' => ProductSale::generateSaleNumber(),
            'sale_date' => now(),
            'total_amount' => $totalAmount,
            'amount_paid' => $request->amount_paid,
            'change_given' => $request->amount_paid - $totalAmount,
            'payment_method' => $request->payment_method,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'notes' => $request->notes,
            'sold_by' => auth()->id(),
        ]);

        foreach ($itemsData as $item) {
            ProductSaleItem::create([
                'product_sale_id' => $sale->id,
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'barcode' => $item['product']->barcode,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
            ]);

            $stockBefore = $item['product']->quantity;
            $item['product']->quantity -= $item['quantity'];
            $item['product']->save();

            StockMovement::create([
                'product_id' => $item['product']->id,
                'type' => 'out',
                'quantity' => $item['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $item['product']->quantity,
                'reason' => 'Sale: ' . $sale->sale_number,
                'reference' => $sale->sale_number,
                'created_by' => auth()->id(),
            ]);
        }

        SchoolIncome::create([
            'date' => now(),
            'category' => 'Product Sales',
            'description' => 'Sale #' . $sale->sale_number,
            'amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'reference_number' => $sale->sale_number,
        ]);

        $lastEntry = CashBookEntry::orderBy('id', 'desc')->first();
        $currentBalance = $lastEntry ? floatval($lastEntry->balance) : 0;
        $newBalance = $currentBalance + $totalAmount;

        CashBookEntry::create([
            'entry_date' => now(),
            'reference_number' => CashBookEntry::generateReferenceNumber(),
            'transaction_type' => 'receipt',
            'category' => 'other_income',
            'description' => '[Product Sale] ' . $sale->sale_number,
            'amount' => $totalAmount,
            'balance' => $newBalance,
            'payment_method' => $request->payment_method,
            'payer_payee' => $request->customer_name ?? 'Walk-in Customer',
            'created_by' => auth()->id(),
            'notes' => 'Auto-generated from Product Sale',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sale completed successfully',
            'sale' => [
                'sale_number' => $sale->sale_number,
                'total_amount' => $totalAmount,
                'change_given' => $sale->change_given,
            ]
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function salesHistory(Request $request)
    {
        $query = ProductSale::with(['items', 'seller']);

        if ($request->date_from) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.finance.products.sales-history', compact('sales'));
    }

    public function saleReceipt($id)
    {
        // Try to find by ID first, then by sale_number if not found
        $sale = ProductSale::with(['items', 'seller'])->find($id);
        
        if (!$sale) {
            // Try to find by sale_number (full or partial match)
            $sale = ProductSale::with(['items', 'seller'])
                ->where('sale_number', $id)
                ->orWhere('sale_number', 'LIKE', '%' . $id)
                ->first();
        }
        
        if (!$sale) {
            abort(404, 'Sale receipt not found');
        }
        
        return view('backend.finance.products.receipt', compact('sale'));
    }

    public function inventory(Request $request)
    {
        $query = Product::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->stock_status === 'in_stock') {
            $query->whereRaw('quantity > min_stock_level');
        } elseif ($request->stock_status === 'low_stock') {
            $query->whereRaw('quantity <= min_stock_level AND quantity > 0');
        } elseif ($request->stock_status === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Product::distinct()->whereNotNull('category')->pluck('category');

        $stats = [
            'total_products' => Product::count(),
            'total_stock_value' => Product::selectRaw('SUM(price * quantity) as total')->first()->total ?? 0,
            'low_stock_count' => Product::whereRaw('quantity <= min_stock_level AND quantity > 0')->count(),
            'out_of_stock_count' => Product::where('quantity', '<=', 0)->count(),
        ];

        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('backend.finance.products.inventory', compact('products', 'categories', 'stats', 'recentMovements'));
    }

    public function stockMovements(Request $request)
    {
        $query = StockMovement::with(['product', 'creator']);

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(30);
        $products = Product::orderBy('name')->get();

        return view('backend.finance.products.stock-movements', compact('movements', 'products'));
    }

    public function categories()
    {
        $categories = ProductCategory::withCount('products')->orderBy('name')->get();
        return view('backend.finance.products.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        ProductCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('finance.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function deleteCategory($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('finance.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function uniformCollections(Request $request)
    {
        $query = UniformCollection::with(['student.user', 'student.class', 'product', 'collector']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->item_type) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->search) {
            $query->whereHas('student.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->orWhereHas('student', function($q) use ($request) {
                $q->where('roll_number', 'like', '%' . $request->search . '%');
            });
        }

        $collections = $query->orderBy('created_at', 'desc')->paginate(20);

        $pendingCount = UniformCollection::where('status', 'pending')->count();
        $collectedCount = UniformCollection::where('status', 'collected')->count();
        $itemTypes = UniformCollection::$itemTypes;

        return view('backend.finance.uniform-collections.index', compact('collections', 'pendingCount', 'collectedCount', 'itemTypes'));
    }

    public function searchStudentForUniform(Request $request)
    {
        $search = $request->search;

        $students = Student::with(['user', 'class'])
            ->where('is_new_student', true)
            ->where(function($q) use ($search) {
                $q->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhere('roll_number', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get()
            ->map(function($student) {
                $pendingUniforms = $student->pendingUniformCollections()->count();
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? 'Unknown',
                    'roll_number' => $student->roll_number,
                    'class' => $student->class->class_name ?? 'N/A',
                    'is_new_student' => $student->is_new_student,
                    'pending_uniforms' => $pendingUniforms,
                ];
            });

        return response()->json(['students' => $students]);
    }

    public function studentUniformHistory($id)
    {
        $student = Student::with(['user', 'class'])->findOrFail($id);
        $pendingCollections = UniformCollection::where('student_id', $id)
            ->where('status', 'pending')
            ->with('product')
            ->get();
        $collectedItems = UniformCollection::where('student_id', $id)
            ->where('status', 'collected')
            ->with(['product', 'collector'])
            ->orderBy('collected_at', 'desc')
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'roll_number' => $student->roll_number,
                'class' => $student->class->class_name ?? 'N/A',
                'is_new_student' => $student->is_new_student,
            ],
            'pending' => $pendingCollections,
            'collected' => $collectedItems,
        ]);
    }

    public function recordUniformCollection(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);
        $collections = [];

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . $product->name
                ], 400);
            }

            $totalPrice = $product->price * $item['quantity'];

            $collection = UniformCollection::create([
                'student_id' => $student->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'size' => $item['size'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $totalPrice,
                'status' => 'collected',
                'collected_at' => now(),
                'collected_by' => auth()->id(),
                'notes' => $request->notes ?? null,
            ]);

            $stockBefore = $product->quantity;
            $product->quantity -= $item['quantity'];
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $item['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $product->quantity,
                'reason' => 'Uniform Collection: ' . ($student->user->name ?? 'Student #' . $student->id),
                'reference' => 'UC-' . $collection->id,
                'created_by' => auth()->id(),
            ]);

            $collections[] = $collection;
        }

        return response()->json([
            'success' => true,
            'message' => 'Uniform collection recorded successfully',
            'collections' => $collections,
        ]);
    }

    public function markUniformCollected($id)
    {
        $collection = UniformCollection::findOrFail($id);

        if ($collection->status === 'collected') {
            return response()->json([
                'success' => false,
                'message' => 'This item has already been collected'
            ], 400);
        }

        $collection->markAsCollected();

        return response()->json([
            'success' => true,
            'message' => 'Item marked as collected',
            'collection' => $collection->load(['student.user', 'collector']),
        ]);
    }

    public function recordItemCollection(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'item_type' => 'required|in:report_card,student_id,certificate,other',
            'item_name' => 'required|string|max:255',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);

        $collection = UniformCollection::create([
            'student_id' => $student->id,
            'item_type' => $request->item_type,
            'item_name' => $request->item_name,
            'academic_year' => $request->academic_year,
            'term' => $request->term,
            'quantity' => 1,
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $request->item_type)) . ' collection recorded',
            'collection' => $collection->load(['student.user']),
        ]);
    }

    public function searchAllStudents(Request $request)
    {
        $search = $request->search;

        $students = Student::with(['user', 'class'])
            ->where(function($q) use ($search) {
                $q->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->orWhere('roll_number', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? 'Unknown',
                    'roll_number' => $student->roll_number,
                    'class' => $student->class->class_name ?? 'N/A',
                    'is_new_student' => $student->is_new_student,
                ];
            });

        return response()->json(['students' => $students]);
    }
}
