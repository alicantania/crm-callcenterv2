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
        'commission_percentage', // ← esto es lo nuevo
    ];
    

    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class);
    }
   

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

}
