<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Led extends Model
{

    use HasFactory;

    protected $fillable = [
        'shelf_number',
        'microcontroller_id',
        'pin_id',
        'status',
    ];

    public function components()
    {
        return $this->hasMany(Component::class);
    }

    public function pin(){
        return $this->belongsTo(Pin::class);
    }

    public function microcontroller(){
        return $this->belongsTo(Microcontroller::class);
    }

}
