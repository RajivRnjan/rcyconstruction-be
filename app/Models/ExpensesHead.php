<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensesHead extends Model
{

    protected $fillable = [
        'name',
        'description',
    ];
}
