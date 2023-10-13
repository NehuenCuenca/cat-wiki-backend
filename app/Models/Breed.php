<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'visits'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

}
