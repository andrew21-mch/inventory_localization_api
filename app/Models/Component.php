<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'image',
        'slug',
        'price_per_unit',
        'cost_price_per_unit',
        'supplier_id',
        'led_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function led()
    {
        return $this->belongsTo(Led::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function outOfStocks()
    {
        return $this->hasMany(OutOfStock::class);
    }




}
