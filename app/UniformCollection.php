<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UniformCollection extends Model
{
    protected $fillable = [
        'student_id',
        'product_id',
        'product_sale_id',
        'product_name',
        'size',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'collected_at',
        'collected_by',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(ProductSale::class, 'product_sale_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function markAsCollected($userId = null)
    {
        $this->update([
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by' => $userId ?? auth()->id(),
        ]);
    }

    public static function getPendingForStudent($studentId)
    {
        return static::where('student_id', $studentId)
            ->where('status', 'pending')
            ->with('product')
            ->get();
    }

    public static function getCollectedForStudent($studentId)
    {
        return static::where('student_id', $studentId)
            ->where('status', 'collected')
            ->with(['product', 'collector'])
            ->orderBy('collected_at', 'desc')
            ->get();
    }
}
