<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrectionRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'attendance_id',
        'user_id',
        'status',
        'reason',
        'original_clock_in',
        'requested_clock_in',
        'original_clock_out',
        'requested_clock_out',
        'original_breaks',
        'requested_breaks',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'original_clock_in' => 'datetime',
            'requested_clock_in' => 'datetime',
            'original_clock_out' => 'datetime',
            'requested_clock_out' => 'datetime',
            'original_breaks' => 'array',
            'requested_breaks' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return $this->isPending() ? '承認待ち' : '承認済み';
    }

    public function statusColor(): string
    {
        return $this->isPending() ? 'text-yellow-600' : 'text-green-600';
    }
}
