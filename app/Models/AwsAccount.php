<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwsAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['account_name', 'access_key', 'secret_key', 'default_region'];

    protected $casts = [
        'access_key' => 'encrypted',
        'secret_key' => 'encrypted',
    ];
}
