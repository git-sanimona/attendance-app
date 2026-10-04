<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // モデル内に定数を定義する（Enumの代わり）
    public const ROLE_USER = 0;

    public const ROLE_ADMIN = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'scheduled_work_start',
        'scheduled_work_end',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'integer', // tinyIntegerの場合もintegerで記述
        // 'email_verified_at',今回はメール認証済みに管理者からの操作はないため不要
    ];

    // 1つのユーザーは複数の勤怠実績を持つ
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // 1つのユーザーは複数の申請を出す
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // 1つのユーザーは複数の月次集計を持つ
    public function monthlyAttendances(): HasMany
    {
        return $this->hasMany(MonthlyAttendance::class);
    }

    // 1つのユーザーは複数のサマリーを持つ
    public function summaryReports(): HasMany
    {
        return $this->hasMany(SummaryReport::class);
    }

    // 【追加リレーション】本日の勤怠実績（複数の勤怠レコードから1対1で日付カラムの最新(最大のデータ)１件を取得）
    public function latestAttendanceRecord(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class)->latestOfMany('date');
    }

    // 【経由リレーション】ユーザーが持つ全ての休憩実績を取得（User -> AttendanceRecord -> AttendanceBreak）
    public function breaks(): HasManyThrough
    {
        return $this->hasManyThrough(AttendanceBreak::class, AttendanceRecord::class);
    }

    // ユーザーか管理者かどうか判定（Policyで使用予定）
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // ユーザーの勤務状態を判定
    protected function attendanceStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $todayRecord = $this->latestAttendanceRecord;

                if (! $todayRecord || $todayRecord->clock_out !== null) {
                    return '勤務外';
                }

                $todayBreak = $todayRecord->breaks()
                    ->whereNull('break_out')
                    ->exists();

                if ($todayBreak) {
                    return '休憩中';
                }

                return '出勤中';
            }
        );
    }
}
