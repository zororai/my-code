<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id',
        'isbn',
        'copy_number',
        'condition',
        'condition_notes',
        'status',
        'added_by',
    ];

    /**
     * Get the book this copy belongs to
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who added this copy
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get all library records for this copy
     */
    public function libraryRecords()
    {
        return $this->hasMany(LibraryRecord::class);
    }

    /**
     * Get active borrow for this copy
     */
    public function activeBorrow()
    {
        return $this->hasOne(LibraryRecord::class)->where('status', 'issued');
    }

    /**
     * Check if copy is available for borrowing
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Get condition badge color
     */
    public function getConditionBadgeAttribute()
    {
        $colors = [
            'excellent' => 'bg-green-100 text-green-800',
            'good' => 'bg-blue-100 text-blue-800',
            'fair' => 'bg-yellow-100 text-yellow-800',
            'poor' => 'bg-orange-100 text-orange-800',
            'damaged' => 'bg-red-100 text-red-800',
        ];

        return $colors[$this->condition] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'available' => 'bg-green-100 text-green-800',
            'borrowed' => 'bg-yellow-100 text-yellow-800',
            'reserved' => 'bg-blue-100 text-blue-800',
            'lost' => 'bg-red-100 text-red-800',
            'damaged' => 'bg-orange-100 text-orange-800',
        ];

        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
