<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ユーザーの日ごとの出退勤と勤務情報を表すModel。
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'memo',
    ];

    /**
     * 勤怠属性のキャスト定義を返す。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
        ];
    }

    /**
     * この勤怠を所有するユーザーを取得する。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この勤怠に紐づく複数の休憩を取得する。
     *
     * @return HasMany<AttendanceBreak, $this>
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * この勤怠に対して提出された修正申請を取得する。
     *
     * @return HasMany<AttendanceCorrectionRequest, $this>
     */
    public function correctionRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    /**
     * 紐づくすべての休憩時間を分単位で合計する。
     */
    public function totalBreakMinutes(): int
    {
        return $this->breaks->sum('break_minutes');
    }

    /**
     * 出退勤の差から休憩時間を除いた勤務時間を分単位で返す。
     */
    public function workedMinutes(): int
    {
        if (! $this->clock_in) {
            return 0;
        }

        $end = $this->clock_out ?? now();

        return max(
            0,
            $this->clock_in->diffInMinutes($end) - $this->totalBreakMinutes()
        );
    }

    /**
     * 合計休憩時間を「○時間○分」形式で返す。
     */
    public function breakTime(): string
    {
        $minutes = $this->totalBreakMinutes();
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}時間{$remainingMinutes}分";
    }

    /**
     * 合計勤務時間を「○時間○分」形式で返す。
     */
    public function workedTime(): string
    {
        $minutes = $this->workedMinutes();
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}時間{$remainingMinutes}分";
    }

    /**
     * 出退勤と進行中休憩から現在の勤務状態を返す。
     */
    public function status(): string
    {
        if (! $this->clock_in) {
            return '未出勤';
        }

        if ($this->clock_out) {
            return '退勤済';
        }

        $isOnBreak = $this->relationLoaded('breaks')
            ? $this->breaks->contains(fn (AttendanceBreak $break) => $break->break_end === null)
            : $this->breaks()->whereNull('break_end')->exists();

        if ($isOnBreak) {
            return '休憩中';
        }

        return '勤務中';
    }

    /**
     * 現在の勤務状態が勤務中か判定する。
     */
    public function isWorking(): bool
    {
        return $this->status() === '勤務中';
    }

    /**
     * 現在の勤務状態が休憩中か判定する。
     */
    public function isOnBreak(): bool
    {
        return $this->status() === '休憩中';
    }

    /**
     * 当日の勤務が終了しているか判定する。
     */
    public function isFinished(): bool
    {
        return $this->status() === '退勤済';
    }

    /**
     * 現在の勤務状態に対応する表示色のTailwind CSSクラスを返す。
     */
    public function statusColor(): string
    {
        return match ($this->status()) {
            '未出勤' => 'text-gray-500',
            '勤務中' => 'text-green-600',
            '休憩中' => 'text-yellow-600',
            '退勤済' => 'text-blue-600',
            default => 'text-gray-500',
        };
    }
}
