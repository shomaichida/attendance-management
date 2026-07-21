<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 一般社員と管理者のアカウント情報を表すModel。
 */
#[Fillable([
    'name',
    'email',
    'password',
    'employee_number',
    'role',
    'department',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ユーザー属性のキャスト定義を返す。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * このユーザーに紐づく勤怠を取得する。
     *
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * このユーザーの当日勤怠を取得する。
     *
     * @return HasOne<Attendance, $this>
     */
    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class)
            ->whereDate('work_date', today())
            ->withDefault();
    }

    /**
     * ユーザーが管理者権限を持つか判定する。
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * ユーザーが一般社員か判定する。
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * ユーザーのアカウントが有効か判定する。
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
