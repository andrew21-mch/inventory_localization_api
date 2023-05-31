<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Microcontroller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'descritpion',
    ];

    public function pins(){
        return $this->hasMany(Pin::class);
    }

    public function leds(){
        return $this->hasMany(Led::class);
    }





}
