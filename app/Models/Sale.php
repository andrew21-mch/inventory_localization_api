<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'quantity',
        'total_price',
        'buyer'
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

}
