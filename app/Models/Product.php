<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'variety', 'cut', 'pack_size_kg', 'unit',
        'price', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'pack_size_kg' => 'decimal:2',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
