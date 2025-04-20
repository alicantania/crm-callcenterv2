<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'available',
        'business_line_id',
    ];

    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class);
    }
}
