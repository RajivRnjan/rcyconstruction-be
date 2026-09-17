<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_details',
        'opening_balance',
        'receipt_amount',
        'payment_amount',
        'balance',
        'details'
    ];
}
