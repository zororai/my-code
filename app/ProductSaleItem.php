<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSaleItem extends Model
{
    protected $fillable = [
        'product_sale_id',
        'product_id',
        'student_id',
        'product_name',
        'barcode',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(ProductSale::class, 'product_sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
