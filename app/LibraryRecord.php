<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class LibraryRecord extends Model
{
    use Auditable;

    protected $fillable = [
        'borrower_type',
        'student_id',
        'teacher_id',
        'issued_by',
        'book_id',
        'book_copy_id',
        'book_title',
        'book_number',
        'copy_isbn',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'notes',
    ];

    protected $dates = [
        'issue_date',
        'due_date',
        'return_date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }

    /**
     * Get the borrower (student or teacher)
     */
    public function getBorrowerAttribute()
    {
        if ($this->borrower_type === 'teacher') {
            return $this->teacher;
        }
        return $this->student;
    }

    /**
     * Get borrower name
     */
    public function getBorrowerNameAttribute()
    {
        if ($this->borrower_type === 'teacher' && $this->teacher) {
            return $this->teacher->user->name ?? 'Unknown Teacher';
        }
        if ($this->student) {
            return $this->student->user->name ?? 'Unknown Student';
        }
        return 'Unknown';
    }
}
