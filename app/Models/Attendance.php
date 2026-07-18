<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function totalBreakMinutes(): int
    {
        return $this->breaks->sum('break_minutes');
    }

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

    public function breakTime(): string
    {
        $minutes = $this->totalBreakMinutes();
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}時間{$remainingMinutes}分";
    }

    public function workedTime(): string
    {
        $minutes = $this->workedMinutes();
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}時間{$remainingMinutes}分";
    }

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

    public function isWorking(): bool
    {
        return $this->status() === '勤務中';
    }

    public function isOnBreak(): bool
    {
        return $this->status() === '休憩中';
    }

    public function isFinished(): bool
    {
        return $this->status() === '退勤済';
    }

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
