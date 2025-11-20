<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'months',
        'monthly_amount',
        'total_amount',
        'is_active'
    ];
}
