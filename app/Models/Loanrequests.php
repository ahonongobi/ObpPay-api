<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loanrequests extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'category',
        'custom_category',
        'amount',
        'status',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
