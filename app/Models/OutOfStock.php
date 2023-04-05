<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutOfStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'quantity',
        'date',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
