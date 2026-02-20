<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LibraryRecord;
use App\Book;
use App\BookCopy;
use App\Student;
use App\Teacher;
use App\User;
use Carbon\Carbon;

class LibraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing of library records
     */
    public function index(Request $request)
    {
        $query = LibraryRecord::with(['student.user', 'student.class', 'teacher.user', 'issuedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by borrower type
        if ($request->filled('borrower_type')) {
            $query->where('borrower_type', $request->borrower_type);
        }

        // Filter by search term (student/teacher name or book)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('book_title', 'like', "%{$search}%")
                  ->orWhere('book_number', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('teacher.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.admin.library.index', compact('records'));
    }

    /**
     * Show form to issue a book
     */
    public function create()
    {
        return view('backend.admin.library.create');
    }

    /**
     * Search students for AJAX autocomplete
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');
        
        $students = Student::with(['user', 'class'])
            ->where('is_transferred', false)
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('roll_number', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? 'Unknown',
                    'roll_number' => $student->roll_number,
                    'class' => $student->class->class_name ?? 'N/A',
                ];
            });

        return response()->json($students);
    }

    /**
     * Search teachers for AJAX autocomplete
     */
    public function searchTeachers(Request $request)
    {
        $search = $request->get('q', '');
        
        $teachers = Teacher::with('user')
            ->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name ?? 'Unknown',
                    'phone' => $teacher->phone ?? 'N/A',
                ];
            });

        return response()->json($teachers);
    }

    /**
     * Store a new library record (issue book)
     */
    public function store(Request $request)
    {
        $borrowerType = $request->input('borrower_type', 'student');
        
        // Validate based on borrower type
        $rules = [
            'borrower_type' => 'required|in:student,teacher',
            'book_id' => 'nullable|exists:books,id',
            'book_copy_id' => 'nullable|exists:book_copies,id',
            'book_title' => 'required|string|max:255',
            'book_number' => 'required|string|max:100',
            'copy_isbn' => 'nullable|string|max:50',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:500',
        ];
        
        if ($borrowerType === 'teacher') {
            $rules['teacher_id'] = 'required|exists:teachers,id';
        } else {
            $rules['student_id'] = 'required|exists:students,id';
        }
        
        $request->validate($rules);

        // If borrowing a specific copy by ISBN
        if ($request->book_copy_id) {
            $copy = BookCopy::findOrFail($request->book_copy_id);
            
            if ($copy->status !== 'available') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This book copy (ISBN: ' . $copy->isbn . ') is not available for borrowing.');
            }

            // Mark copy as borrowed
            $copy->update(['status' => 'borrowed']);
            
            // Update parent book available quantity
            $book = $copy->book;
            $book->decrement('available_quantity');
            if ($book->available_quantity <= 0) {
                $book->update(['status' => 'borrowed']);
            }
        }
        // Legacy: If a book from archive is selected without specific copy
        elseif ($request->book_id) {
            $book = Book::findOrFail($request->book_id);
            
            if ($book->available_quantity <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This book is not available for borrowing.');
            }

            $book->decrement('available_quantity');
            
            // Update book status if all copies are borrowed
            if ($book->available_quantity <= 0) {
                $book->update(['status' => 'borrowed']);
            }
        }

        $recordData = [
            'borrower_type' => $borrowerType,
            'issued_by' => auth()->id(),
            'book_id' => $request->book_id,
            'book_copy_id' => $request->book_copy_id,
            'book_title' => $request->book_title,
            'book_number' => $request->book_number,
            'copy_isbn' => $request->copy_isbn,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'status' => 'issued',
            'notes' => $request->notes,
        ];
        
        if ($borrowerType === 'teacher') {
            $recordData['teacher_id'] = $request->teacher_id;
            $recordData['student_id'] = null;
        } else {
            $recordData['student_id'] = $request->student_id;
            $recordData['teacher_id'] = null;
        }

        LibraryRecord::create($recordData);

        return redirect()->route('admin.library.index')
            ->with('success', 'Book issued successfully!');
    }

    /**
     * Show a specific library record
     */
    public function show($id)
    {
        $record = LibraryRecord::with(['student.user', 'student.class', 'issuedBy'])->findOrFail($id);
        return view('backend.admin.library.show', compact('record'));
    }

    /**
     * Mark book as returned
     */
    public function returnBook(Request $request, $id)
    {
        $record = LibraryRecord::findOrFail($id);
        
        // If a specific copy was borrowed, mark it as available
        if ($record->book_copy_id) {
            $copy = BookCopy::find($record->book_copy_id);
            if ($copy) {
                $copy->update(['status' => 'available']);
                
                // Update parent book available quantity
                if ($copy->book) {
                    $copy->book->increment('available_quantity');
                    $copy->book->update(['status' => 'available']);
                }
            }
        }
        // Legacy: If book was from archive without specific copy
        elseif ($record->book_id) {
            $book = Book::find($record->book_id);
            if ($book) {
                $book->increment('available_quantity');
                $book->update(['status' => 'available']);
            }
        }
        
        $record->update([
            'return_date' => Carbon::now(),
            'status' => 'returned',
        ]);

        return redirect()->back()->with('success', 'Book marked as returned!');
    }

    /**
     * Delete a library record
     */
    public function destroy($id)
    {
        $record = LibraryRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('admin.library.index')
            ->with('success', 'Library record deleted successfully!');
    }

    /**
     * View library history for a specific student
     */
    public function studentHistory($studentId)
    {
        $student = Student::with(['user', 'class'])->findOrFail($studentId);
        $records = LibraryRecord::where('student_id', $studentId)
            ->with('issuedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.admin.library.student-history', compact('student', 'records'));
    }

    /**
     * View library history for a specific teacher
     */
    public function teacherHistory($teacherId)
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);
        $records = LibraryRecord::where('teacher_id', $teacherId)
            ->where('borrower_type', 'teacher')
            ->with('issuedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.admin.library.teacher-history', compact('teacher', 'records'));
    }

    /**
     * Teacher's own library view - shows their borrowed books
     */
    public function myTeacherLibrary()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        
        if (!$teacher) {
            return redirect()->route('home')->with('error', 'Teacher profile not found.');
        }

        $currentlyBorrowed = LibraryRecord::where('teacher_id', $teacher->id)
            ->where('borrower_type', 'teacher')
            ->where('status', 'issued')
            ->with('issuedBy')
            ->orderBy('issue_date', 'desc')
            ->get();

        $borrowingHistory = LibraryRecord::where('teacher_id', $teacher->id)
            ->where('borrower_type', 'teacher')
            ->where('status', 'returned')
            ->with('issuedBy')
            ->orderBy('return_date', 'desc')
            ->limit(20)
            ->get();

        return view('backend.teacher.library', compact('teacher', 'currentlyBorrowed', 'borrowingHistory'));
    }

    /**
     * Display listing of all books
     */
    public function books(Request $request)
    {
        $query = Book::with('addedBy');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('book_number', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.admin.library.books.index', compact('books'));
    }

    /**
     * Show form to add a new book
     */
    public function createBook()
    {
        return view('backend.admin.library.books.create');
    }

    /**
     * Store a new book
     */
    public function storeBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'book_number' => 'required|string|max:100|unique:books,book_number',
            'condition' => 'required|in:excellent,good,fair,poor,damaged',
            'condition_notes' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ]);

        $data = $request->only([
            'title', 'book_number', 'condition', 'condition_notes',
            'author', 'isbn', 'category', 'quantity'
        ]);

        $data['available_quantity'] = $request->quantity;
        $data['added_by'] = auth()->id();
        $data['status'] = 'available';

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/books'), $imageName);
            $data['image'] = 'uploads/books/' . $imageName;
        }

        Book::create($data);

        return redirect()->route('admin.library.books')
            ->with('success', 'Book added successfully!');
    }

    /**
     * Show form to edit a book
     */
    public function editBook($id)
    {
        $book = Book::findOrFail($id);
        return view('backend.admin.library.books.edit', compact('book'));
    }

    /**
     * Update a book
     */
    public function updateBook(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'book_number' => 'required|string|max:100|unique:books,book_number,' . $id,
            'condition' => 'required|in:excellent,good,fair,poor,damaged',
            'condition_notes' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ]);

        $data = $request->only([
            'title', 'book_number', 'condition', 'condition_notes',
            'author', 'isbn', 'category', 'quantity'
        ]);

        // Adjust available quantity based on quantity change
        $quantityDiff = $request->quantity - $book->quantity;
        $data['available_quantity'] = max(0, $book->available_quantity + $quantityDiff);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image && file_exists(public_path($book->image))) {
                unlink(public_path($book->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/books'), $imageName);
            $data['image'] = 'uploads/books/' . $imageName;
        }

        $book->update($data);

        return redirect()->route('admin.library.books')
            ->with('success', 'Book updated successfully!');
    }

    /**
     * View book history (who borrowed this book)
     */
    public function bookHistory($id)
    {
        $book = Book::with('addedBy')->findOrFail($id);
        $records = LibraryRecord::where('book_id', $id)
            ->orWhere(function ($q) use ($book) {
                $q->where('book_number', $book->book_number);
            })
            ->with(['student.user', 'student.class', 'issuedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.admin.library.books.history', compact('book', 'records'));
    }

    /**
     * Delete a book
     */
    public function destroyBook($id)
    {
        $book = Book::findOrFail($id);

        // Check if book has active borrows
        $activeBorrows = LibraryRecord::where('book_id', $id)
            ->where('status', 'issued')
            ->count();

        if ($activeBorrows > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete book with active borrows. Please return all copies first.');
        }

        // Delete image if exists
        if ($book->image && file_exists(public_path($book->image))) {
            unlink(public_path($book->image));
        }

        $book->delete();

        return redirect()->route('admin.library.books')
            ->with('success', 'Book deleted successfully!');
    }

    /**
     * Search books for AJAX autocomplete
     */
    public function searchBooks(Request $request)
    {
        $search = $request->get('q', '');
        
        $books = Book::where('available_quantity', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('book_number', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'book_number' => $book->book_number,
                    'author' => $book->author ?? 'Unknown',
                    'available' => $book->available_quantity,
                    'condition' => ucfirst($book->condition),
                ];
            });

        return response()->json($books);
    }

    /**
     * Search book copies by ISBN for borrowing
     */
    public function searchBookCopies(Request $request)
    {
        $search = $request->get('q', '');
        
        $copies = BookCopy::with('book')
            ->where('status', 'available')
            ->where(function ($query) use ($search) {
                $query->where('isbn', 'like', "%{$search}%")
                    ->orWhere('copy_number', 'like', "%{$search}%")
                    ->orWhereHas('book', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('book_number', 'like', "%{$search}%");
                    });
            })
            ->limit(15)
            ->get()
            ->map(function ($copy) {
                return [
                    'id' => $copy->id,
                    'isbn' => $copy->isbn,
                    'copy_number' => $copy->copy_number,
                    'book_id' => $copy->book_id,
                    'title' => $copy->book->title ?? 'Unknown',
                    'book_number' => $copy->book->book_number ?? '',
                    'author' => $copy->book->author ?? 'Unknown',
                    'condition' => ucfirst($copy->condition),
                ];
            });

        return response()->json($copies);
    }

    /**
     * View book copies for a specific book
     */
    public function bookCopies($bookId)
    {
        $book = Book::with(['copies.addedBy', 'copies.activeBorrow'])->findOrFail($bookId);
        return view('backend.admin.library.books.copies', compact('book'));
    }

    /**
     * Store multiple book copies with unique ISBNs
     */
    public function storeBookCopies(Request $request, $bookId)
    {
        $book = Book::findOrFail($bookId);

        $request->validate([
            'copies' => 'required|array|min:1',
            'copies.*.isbn' => 'required|string|max:50|unique:book_copies,isbn',
            'copies.*.condition' => 'required|in:excellent,good,fair,poor,damaged',
            'copies.*.condition_notes' => 'nullable|string|max:500',
        ]);

        $copiesAdded = 0;
        foreach ($request->copies as $index => $copyData) {
            BookCopy::create([
                'book_id' => $book->id,
                'isbn' => $copyData['isbn'],
                'copy_number' => $book->book_number . '-' . ($book->copies()->count() + $copiesAdded + 1),
                'condition' => $copyData['condition'],
                'condition_notes' => $copyData['condition_notes'] ?? null,
                'status' => 'available',
                'added_by' => auth()->id(),
            ]);
            $copiesAdded++;
        }

        // Update book quantity
        $book->increment('quantity', $copiesAdded);
        $book->increment('available_quantity', $copiesAdded);

        return redirect()->route('admin.library.books.copies', $book->id)
            ->with('success', "{$copiesAdded} book copies added successfully!");
    }

    /**
     * Show form to add copies to a book
     */
    public function createBookCopies($bookId)
    {
        $book = Book::findOrFail($bookId);
        return view('backend.admin.library.books.add-copies', compact('book'));
    }

    /**
     * Student view - My Library (borrowed books and history)
     */
    public function myLibrary()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('home')->with('error', 'Student record not found.');
        }

        // Get currently borrowed books (status = issued)
        $borrowedBooks = LibraryRecord::where('student_id', $student->id)
            ->where('status', 'issued')
            ->with(['book', 'issuedBy'])
            ->orderBy('issue_date', 'desc')
            ->get();

        // Get borrowing history (all records)
        $borrowingHistory = LibraryRecord::where('student_id', $student->id)
            ->with(['book', 'issuedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats
        $totalBorrowed = LibraryRecord::where('student_id', $student->id)->count();
        $currentlyBorrowed = $borrowedBooks->count();
        $returned = LibraryRecord::where('student_id', $student->id)->where('status', 'returned')->count();
        $overdue = $borrowedBooks->filter(function ($record) {
            return $record->due_date && $record->due_date < now();
        })->count();

        return view('backend.student.library', compact(
            'student', 'borrowedBooks', 'borrowingHistory',
            'totalBorrowed', 'currentlyBorrowed', 'returned', 'overdue'
        ));
    }
}
