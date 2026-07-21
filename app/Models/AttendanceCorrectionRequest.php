<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 一般ユーザーが提出した勤怠修正申請と承認状態を表すModel。
 */
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

    /**
     * 修正申請属性のキャスト定義を返す。
     *
     * @return array<string, string>
     */
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

    /**
     * 修正対象の勤怠を取得する。
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 修正申請を提出したユーザーを取得する。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 修正申請を承認した管理者を取得する。
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 修正申請が承認待ちか判定する。
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 承認状態の日本語表示名を返す。
     */
    public function statusLabel(): string
    {
        return $this->isPending() ? '承認待ち' : '承認済み';
    }

    /**
     * 承認状態に対応する表示色のTailwind CSSクラスを返す。
     */
    public function statusColor(): string
    {
        return $this->isPending() ? 'text-yellow-600' : 'text-green-600';
    }
}
