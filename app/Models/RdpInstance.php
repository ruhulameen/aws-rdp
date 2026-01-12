<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RdpInstance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'aws_account_id', 'instance_id', 'region', 'key_name',
        'public_ip', 'username', 'password', 'status',
        'group_id'
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function awsAccount(): BelongsTo
    {
        return $this->belongsTo(AwsAccount::class);
    }
}
