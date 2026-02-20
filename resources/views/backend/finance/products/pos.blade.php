@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Point of Sale</h1>
            <p class="text-gray-600">Scan barcode or search products to make a sale</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.products.student-purchases') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Student Purchases</a>
            <a href="{{ route('finance.uniform-collections') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Uniform Collections</a>
            <a href="{{ route('finance.products.sales-history') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Sales History</a>
            <a href="{{ route('finance.products') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Back to Products</a>
        </div>
    </div>

    <!-- Sale Mode Tabs -->
    <div class="mb-6">
        <div class="flex border-b border-gray-200">
            <button onclick="switchMode('regular')" id="regularTab" class="px-6 py-3 text-sm font-medium border-b-2 border-green-500 text-green-600 bg-green-50">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Regular Sale
            </button>
            <button onclick="switchMode('uniform')" id="uniformTab" class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Student Uniform Collection
            </button>
        </div>
    </div>

    <!-- Uniform Collection Mode -->
    <div id="uniformMode" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Student Search -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search New Student
                    </h3>
                    <div class="flex gap-2">
                        <input type="text" id="studentSearchInput" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Search by student name or roll number..." onkeyup="searchStudents()">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Only new students are shown for uniform collection</p>
                    
                    <div id="studentSearchResults" class="mt-4 space-y-2 max-h-64 overflow-y-auto hidden"></div>
                </div>

                <!-- Selected Student Info -->
                <div id="selectedStudentPanel" class="bg-white rounded-lg shadow-sm border p-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Selected Student</h3>
                    <div id="selectedStudentInfo" class="flex items-center justify-between bg-purple-50 p-4 rounded-lg">
                        <!-- Student info will be populated here -->
                    </div>
                    <button onclick="clearSelectedStudent()" class="mt-3 text-sm text-red-600 hover:text-red-800">Clear Selection</button>
                </div>

                <!-- Uniform Products -->
                <div id="uniformProductsPanel" class="bg-white rounded-lg shadow-sm border p-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Select Uniform Items</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-96 overflow-y-auto">
                        @foreach($products as $product)
                        @if(stripos($product->category ?? '', 'uniform') !== false || stripos($product->name, 'uniform') !== false || stripos($product->name, 'shirt') !== false || stripos($product->name, 'trouser') !== false || stripos($product->name, 'skirt') !== false || stripos($product->name, 'shoe') !== false || stripos($product->name, 'tie') !== false || stripos($product->name, 'blazer') !== false || stripos($product->name, 'jersey') !== false)
                        <button onclick="addToUniformCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->quantity }})" 
                            class="p-3 border rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors text-left">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-16 object-cover rounded mb-2">
                            @else
                            <div class="w-full h-16 bg-purple-100 rounded mb-2 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            @endif
                            <div class="font-medium text-sm text-gray-800 truncate">{{ $product->name }}</div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-purple-600 font-bold">${{ number_format($product->price, 2) }}</span>
                                <span class="text-xs text-gray-500">Qty: {{ $product->quantity }}</span>
                            </div>
                        </button>
                        @endif
                        @endforeach
                    </div>
                    <p class="text-sm text-gray-500 mt-4">Showing uniform-related products only. <a href="{{ route('finance.products') }}" class="text-purple-600 hover:underline">Manage products</a></p>
                </div>
            </div>

            <!-- Right: Uniform Cart -->
            <div class="bg-white rounded-lg shadow-sm border p-6 h-fit sticky top-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Uniform Collection
                </h3>

                <div id="uniformCartItems" class="space-y-3 max-h-64 overflow-y-auto mb-4">
                    <p class="text-gray-500 text-center py-8" id="emptyUniformCartMessage">Select a student and add uniform items</p>
                </div>

                <div class="border-t pt-4 space-y-3">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total Items:</span>
                        <span id="uniformCartTotal">0</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="uniformNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Any additional notes..."></textarea>
                    </div>

                    <button onclick="recordUniformCollection()" id="uniformCheckoutBtn" disabled class="w-full bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold">
                        Record Collection
                    </button>
                    <button onclick="clearUniformCart()" class="w-full bg-red-100 text-red-600 py-2 rounded-lg hover:bg-red-200">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Regular Sale Mode -->
    <div id="regularMode" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Product Search & Barcode Scanner -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Barcode Scanner -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    Scan Barcode
                </h3>
                <div class="flex gap-2">
                    <input type="text" id="barcodeInput" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg font-mono" placeholder="Scan or enter barcode..." autofocus>
                    <button onclick="searchBarcode()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-2">Press Enter after scanning or click Search</p>
            </div>

            <!-- Product Grid -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Select Products</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-96 overflow-y-auto">
                    @foreach($products as $product)
                    <button onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->quantity }}, '{{ $product->barcode }}')" 
                        class="p-3 border rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors text-left">
                        @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-16 object-cover rounded mb-2">
                        @else
                        <div class="w-full h-16 bg-gray-100 rounded mb-2 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        @endif
                        <div class="font-medium text-sm text-gray-800 truncate">{{ $product->name }}</div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-green-600 font-bold">${{ number_format($product->price, 2) }}</span>
                            <span class="text-xs text-gray-500">Qty: {{ $product->quantity }}</span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Cart -->
        <div class="bg-white rounded-lg shadow-sm border p-6 h-fit sticky top-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Cart
            </h3>

            <div id="cartItems" class="space-y-3 max-h-64 overflow-y-auto mb-4">
                <p class="text-gray-500 text-center py-8" id="emptyCartMessage">No items in cart</p>
            </div>

            <div class="border-t pt-4 space-y-3">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span id="cartTotal">$0.00</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Received</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                        <input type="number" id="amountPaid" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="0.00" onkeyup="calculateChange()">
                    </div>
                </div>

                <div class="flex justify-between text-lg">
                    <span>Change:</span>
                    <span id="changeAmount" class="font-bold text-green-600">$0.00</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Name</label>
                    <input type="text" id="customerName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Enter buyer name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" id="customerPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Enter phone number">
                </div>

                <button onclick="processSale()" id="checkoutBtn" disabled class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold">
                    Complete Sale
                </button>
                <button onclick="clearCart()" class="w-full bg-red-100 text-red-600 py-2 rounded-lg hover:bg-red-200">
                    Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Student Search Modal for Cart Items -->
<div id="itemStudentSearchModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[80vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Attach Student to Item</h3>
            <button onclick="closeStudentSearchModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <input type="text" id="itemStudentSearchInput" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-4" placeholder="Search student by name or roll number..." onkeyup="searchItemStudent()">
        <div id="itemStudentSearchResults" class="flex-1 overflow-y-auto border rounded-lg">
            <p class="text-gray-500 text-center py-8">Type to search for students</p>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Sale Completed!</h3>
            <p id="saleDetails" class="text-gray-600 mb-4"></p>
            <div class="flex gap-3 justify-center">
                <button onclick="printReceipt()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Print Receipt</button>
                <button onclick="closeModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">New Sale</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let lastSaleNumber = '';
let cartStudentSearchTimeout = null;
let editingStudentForItemId = null;

document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchBarcode();
    }
});

function searchBarcode() {
    const barcode = document.getElementById('barcodeInput').value.trim();
    if (!barcode) return;

    fetch('{{ route("finance.products.find-by-barcode") }}?barcode=' + barcode)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                addToCart(data.product.id, data.product.name, data.product.price, data.product.quantity, data.product.barcode);
                document.getElementById('barcodeInput').value = '';
            } else {
                alert(data.message);
            }
        })
        .catch(err => alert('Error searching product'));
    
    document.getElementById('barcodeInput').focus();
}

function addToCart(id, name, price, stock, barcode) {
    const existing = cart.find(item => item.id === id && !item.student_id);
    
    if (existing) {
        if (existing.quantity >= stock) {
            alert('Cannot add more - only ' + stock + ' in stock');
            return;
        }
        existing.quantity++;
    } else {
        cart.push({ id, name, price, stock, barcode, quantity: 1, student_id: null, student_name: null, student_class: null });
    }
    
    updateCartDisplay();
}

function updateQuantity(id, change) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    
    const newQty = item.quantity + change;
    if (newQty < 1) {
        removeFromCart(id);
        return;
    }
    if (newQty > item.stock) {
        alert('Cannot add more - only ' + item.stock + ' in stock');
        return;
    }
    
    item.quantity = newQty;
    updateCartDisplay();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
}

function removeFromCartByIndex(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function openStudentSearchForItem(index) {
    editingStudentForItemId = index;
    document.getElementById('itemStudentSearchModal').classList.remove('hidden');
    document.getElementById('itemStudentSearchInput').value = '';
    document.getElementById('itemStudentSearchInput').focus();
    document.getElementById('itemStudentSearchResults').innerHTML = '';
}

function closeStudentSearchModal() {
    document.getElementById('itemStudentSearchModal').classList.add('hidden');
    editingStudentForItemId = null;
}

function searchItemStudent() {
    clearTimeout(cartStudentSearchTimeout);
    const search = document.getElementById('itemStudentSearchInput').value.trim();
    
    if (search.length < 2) {
        document.getElementById('itemStudentSearchResults').innerHTML = '<p class="text-gray-500 text-center py-4">Type at least 2 characters</p>';
        return;
    }
    
    cartStudentSearchTimeout = setTimeout(() => {
        fetch('{{ route("finance.products.search-students") }}?search=' + encodeURIComponent(search))
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('itemStudentSearchResults');
                if (data.students.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">No students found</p>';
                } else {
                    container.innerHTML = data.students.map(student => `
                        <button type="button" onclick="selectStudentForItem(${student.id}, '${student.name.replace(/'/g, "\\'")}', '${student.class}')" 
                            class="w-full flex items-center justify-between p-3 hover:bg-blue-50 text-left border-b last:border-b-0">
                            <div>
                                <div class="font-medium text-gray-900">${student.name}</div>
                                <div class="text-sm text-gray-500">${student.roll_number} • ${student.class}</div>
                            </div>
                        </button>
                    `).join('');
                }
            })
            .catch(err => console.error('Search error:', err));
    }, 300);
}

function selectStudentForItem(studentId, studentName, studentClass) {
    if (editingStudentForItemId !== null && cart[editingStudentForItemId]) {
        cart[editingStudentForItemId].student_id = studentId;
        cart[editingStudentForItemId].student_name = studentName;
        cart[editingStudentForItemId].student_class = studentClass;
        updateCartDisplay();
    }
    closeStudentSearchModal();
}

function clearItemStudent(index) {
    if (cart[index]) {
        cart[index].student_id = null;
        cart[index].student_name = null;
        cart[index].student_class = null;
        updateCartDisplay();
    }
}

function clearCart() {
    cart = [];
    updateCartDisplay();
}

function updateCartDisplay() {
    const container = document.getElementById('cartItems');
    const emptyMsg = document.getElementById('emptyCartMessage');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8" id="emptyCartMessage">No items in cart</p>';
        checkoutBtn.disabled = true;
    } else {
        container.innerHTML = cart.map((item, index) => `
            <div class="bg-gray-50 p-2 rounded mb-2">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="font-medium text-sm">${item.name}</div>
                        <div class="text-xs text-gray-500">$${item.price.toFixed(2)} each</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQuantity(${item.id}, -1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">-</button>
                        <span class="w-8 text-center">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, 1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">+</button>
                        <span class="w-16 text-right font-semibold">$${(item.price * item.quantity).toFixed(2)}</span>
                        <button onclick="removeFromCartByIndex(${index})" class="text-red-500 hover:text-red-700 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-1 flex items-center gap-2">
                    ${item.student_id ? `
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            ${item.student_name}
                            <button onclick="clearItemStudent(${index})" class="ml-1 text-blue-600 hover:text-blue-800">&times;</button>
                        </span>
                    ` : `
                        <button onclick="openStudentSearchForItem(${index})" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Attach Student
                        </button>
                    `}
                </div>
            </div>
        `).join('');
        checkoutBtn.disabled = false;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
    calculateChange();
}

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = Math.max(0, paid - total);
    document.getElementById('changeAmount').textContent = '$' + change.toFixed(2);
}

function processSale() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
    
    if (amountPaid < total) {
        alert('Insufficient payment amount');
        return;
    }
    
    const data = {
        items: cart.map(item => ({ product_id: item.id, quantity: item.quantity, student_id: item.student_id })),
        amount_paid: amountPaid,
        payment_method: document.getElementById('paymentMethod').value,
        customer_name: document.getElementById('customerName').value || null,
        customer_phone: document.getElementById('customerPhone').value || null,
    };
    
    fetch('{{ route("finance.products.process-sale") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            lastSaleNumber = data.sale.sale_number;
            document.getElementById('saleDetails').innerHTML = `
                Sale #${data.sale.sale_number}<br>
                Total: $${parseFloat(data.sale.total_amount).toFixed(2)}<br>
                Change: $${parseFloat(data.sale.change_given).toFixed(2)}
            `;
            document.getElementById('successModal').classList.remove('hidden');
            clearCart();
            document.getElementById('amountPaid').value = '';
            document.getElementById('customerName').value = '';
            document.getElementById('customerPhone').value = '';
        } else {
            alert(data.message || 'Sale failed');
        }
    })
    .catch(err => {
        console.error('Sale error:', err);
        alert('Error processing sale: ' + (err.message || 'Unknown error'));
    });
}

function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('barcodeInput').focus();
}

function printReceipt() {
    window.open('{{ url("finance/products/sales") }}/' + lastSaleNumber.split('-').pop() + '/receipt', '_blank');
}

// ========== UNIFORM COLLECTION MODE ==========
let currentMode = 'regular';
let selectedStudent = null;
let uniformCart = [];
let searchTimeout = null;

function switchMode(mode) {
    currentMode = mode;
    
    const regularTab = document.getElementById('regularTab');
    const uniformTab = document.getElementById('uniformTab');
    const regularMode = document.getElementById('regularMode');
    const uniformMode = document.getElementById('uniformMode');
    
    if (mode === 'regular') {
        regularTab.className = 'px-6 py-3 text-sm font-medium border-b-2 border-green-500 text-green-600 bg-green-50';
        uniformTab.className = 'px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
        regularMode.classList.remove('hidden');
        uniformMode.classList.add('hidden');
    } else {
        uniformTab.className = 'px-6 py-3 text-sm font-medium border-b-2 border-purple-500 text-purple-600 bg-purple-50';
        regularTab.className = 'px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
        uniformMode.classList.remove('hidden');
        regularMode.classList.add('hidden');
    }
}

function searchStudents() {
    clearTimeout(searchTimeout);
    const search = document.getElementById('studentSearchInput').value.trim();
    
    if (search.length < 2) {
        document.getElementById('studentSearchResults').classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('{{ route("finance.uniform-collections.search-student") }}?search=' + encodeURIComponent(search))
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('studentSearchResults');
                if (data.students.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">No new students found</p>';
                } else {
                    container.innerHTML = data.students.map(student => `
                        <button onclick="selectStudent(${student.id}, '${student.name}', '${student.roll_number}', '${student.class}')" 
                            class="w-full flex items-center justify-between p-3 border rounded-lg hover:bg-purple-50 hover:border-purple-300 text-left">
                            <div>
                                <div class="font-medium text-gray-900">${student.name}</div>
                                <div class="text-sm text-gray-500">${student.roll_number} • ${student.class}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">New Student</span>
                                ${student.pending_uniforms > 0 ? `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">${student.pending_uniforms} pending</span>` : ''}
                            </div>
                        </button>
                    `).join('');
                }
                container.classList.remove('hidden');
            })
            .catch(err => console.error('Search error:', err));
    }, 300);
}

function selectStudent(id, name, rollNumber, className) {
    selectedStudent = { id, name, roll_number: rollNumber, class: className };
    
    document.getElementById('studentSearchResults').classList.add('hidden');
    document.getElementById('studentSearchInput').value = '';
    
    document.getElementById('selectedStudentInfo').innerHTML = `
        <div>
            <div class="font-semibold text-gray-900">${name}</div>
            <div class="text-sm text-gray-600">${rollNumber} • ${className}</div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">New Student</span>
    `;
    
    document.getElementById('selectedStudentPanel').classList.remove('hidden');
    document.getElementById('uniformProductsPanel').classList.remove('hidden');
    
    updateUniformCartDisplay();
}

function clearSelectedStudent() {
    selectedStudent = null;
    uniformCart = [];
    document.getElementById('selectedStudentPanel').classList.add('hidden');
    document.getElementById('uniformProductsPanel').classList.add('hidden');
    updateUniformCartDisplay();
}

function addToUniformCart(id, name, price, stock) {
    if (!selectedStudent) {
        alert('Please select a student first');
        return;
    }
    
    const existing = uniformCart.find(item => item.id === id);
    
    if (existing) {
        if (existing.quantity >= stock) {
            alert('Cannot add more - only ' + stock + ' in stock');
            return;
        }
        existing.quantity++;
    } else {
        uniformCart.push({ id, name, price, stock, quantity: 1, size: '' });
    }
    
    updateUniformCartDisplay();
}

function updateUniformQuantity(id, change) {
    const item = uniformCart.find(i => i.id === id);
    if (!item) return;
    
    const newQty = item.quantity + change;
    if (newQty < 1) {
        removeFromUniformCart(id);
        return;
    }
    if (newQty > item.stock) {
        alert('Cannot add more - only ' + item.stock + ' in stock');
        return;
    }
    
    item.quantity = newQty;
    updateUniformCartDisplay();
}

function removeFromUniformCart(id) {
    uniformCart = uniformCart.filter(item => item.id !== id);
    updateUniformCartDisplay();
}

function clearUniformCart() {
    uniformCart = [];
    updateUniformCartDisplay();
}

function updateUniformCartDisplay() {
    const container = document.getElementById('uniformCartItems');
    const checkoutBtn = document.getElementById('uniformCheckoutBtn');
    
    if (uniformCart.length === 0 || !selectedStudent) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8" id="emptyUniformCartMessage">Select a student and add uniform items</p>';
        checkoutBtn.disabled = true;
    } else {
        container.innerHTML = uniformCart.map(item => `
            <div class="flex items-center justify-between bg-purple-50 p-2 rounded">
                <div class="flex-1">
                    <div class="font-medium text-sm">${item.name}</div>
                    <div class="text-xs text-gray-500">$${item.price.toFixed(2)} each</div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="updateUniformQuantity(${item.id}, -1)" class="w-6 h-6 bg-purple-200 rounded hover:bg-purple-300">-</button>
                    <span class="w-8 text-center">${item.quantity}</span>
                    <button onclick="updateUniformQuantity(${item.id}, 1)" class="w-6 h-6 bg-purple-200 rounded hover:bg-purple-300">+</button>
                    <button onclick="removeFromUniformCart(${item.id})" class="text-red-500 hover:text-red-700 ml-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
        checkoutBtn.disabled = false;
    }
    
    const totalItems = uniformCart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById('uniformCartTotal').textContent = totalItems;
}

function recordUniformCollection() {
    if (!selectedStudent || uniformCart.length === 0) {
        alert('Please select a student and add uniform items');
        return;
    }
    
    const data = {
        student_id: selectedStudent.id,
        items: uniformCart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            size: item.size || null
        })),
        notes: document.getElementById('uniformNotes').value || null
    };
    
    fetch('{{ route("finance.uniform-collections.record") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Uniform collection recorded successfully for ' + selectedStudent.name);
            clearSelectedStudent();
            document.getElementById('uniformNotes').value = '';
        } else {
            alert(data.message || 'Failed to record collection');
        }
    })
    .catch(err => {
        console.error('Collection error:', err);
        alert('Error recording collection: ' + (err.message || 'Unknown error'));
    });
}
</script>
@endsection
