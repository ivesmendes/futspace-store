<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShirtCounter extends Model
{
    use HasFactory;

    protected $table = 'shirt_counter';
    protected $fillable = ['count'];
}
