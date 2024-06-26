<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';

    protected $fillable = [
        'payer_id',
        'payee_id',
        'value',
        'is_completed',
        'observation',
        'datetime_init',
        'datetime_finish',
    ];

    protected $casts = [
        'value' => 'integer',
        'is_completed' => 'boolean',
        'datetime_init' => 'datetime',
        'datetime_finish' => 'datetime',
    ];

    public $timestamps = false;

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function createTransfer(array $transfer): Transfer
    {
        return $this->create($transfer);
    }

    public function initTransfer(int $payerId, int $payeeId, int $value): Transfer
    {
        return $this->create([
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'value' => $value,
            'is_completed' => false,
            'datetime_init' => now(),
        ]);
    }

    public function finishTransferCompleted(int $transferId, string|null $observation): bool
    {
        return $this->where('id', $transferId)
            ->update([
                'is_completed' => true,
                'datetime_finish' => now(),
                'observation' => $observation
            ]);
    }

    public function finishTransferNotCompleted(int $transferId, string|null $observation): bool
    {
        return $this->where('id', $transferId)
            ->update([
                'is_completed' => false,
                'datetime_finish' => now(),
                'observation' => $observation
            ]);
    }
}
