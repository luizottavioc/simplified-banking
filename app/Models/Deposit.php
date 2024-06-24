<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teller_id',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taller_id');
    }

    public function createDeposit($deposit): Deposit
    {
        return $this->create($deposit);
    }

    public function createWithdraw($withdraw): Deposit
    {
        return $this->create($withdraw);
    }
}
