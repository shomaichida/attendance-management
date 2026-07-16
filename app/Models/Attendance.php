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
}
