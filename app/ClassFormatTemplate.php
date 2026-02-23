<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFormatTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'values',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the class names as an array
     */
    public function getClassNamesArray()
    {
        if ($this->type === 'numeric') {
            $count = (int) $this->values;
            $names = [];
            for ($i = 1; $i <= $count; $i++) {
                $names[] = ".{$i}";
            }
            return $names;
        }

        // For names and custom types, split by comma
        return array_map('trim', explode(',', $this->values));
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
