<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    use HasFactory;

    protected $fillable = [
        'microntroller_id',
        'pinNumber',
    ];

    public function microController(){
        return $this->belongsTo(Microcontroller::class);
    }

    public function led(){
        return $this->hasOne(Led::class);
    }


}
