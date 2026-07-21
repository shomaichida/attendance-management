<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 勤怠に紐づく1回分の休憩情報を表すModel。
 */
class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_minutes',
    ];

    /**
     * 休憩属性のキャスト定義を返す。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'break_start' => 'datetime',
            'break_end' => 'datetime',
            'break_minutes' => 'integer',
        ];
    }

    /**
     * この休憩が属する勤怠を取得する。
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
