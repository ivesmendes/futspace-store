<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loss extends Model
{
    use HasFactory;

    protected $fillable = ['reason', 'amount', 'loss_date'];

    protected $casts = [
        'amount' => 'float',
        'loss_date' => 'date:Y-m-d',
    ];
}
