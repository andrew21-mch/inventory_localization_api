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

    public function m_c_u_s(){
        return $this->belongsTo(MCU::class);
    }

    public function led(){
        return $this->hasOne(Led::class);
    }


}
