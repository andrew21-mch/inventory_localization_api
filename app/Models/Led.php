<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Led extends Model
{

    use HasFactory;

    protected $fillable = [
        'led_unique_number',
        'shelf_number'
    ];

    public function components()
    {
        return $this->hasMany(Component::class);
    }

}
