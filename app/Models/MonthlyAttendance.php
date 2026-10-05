<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
        ];
    }

    // 一月の勤怠は1人のユーザーに紐づく
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 一月の勤怠は複数の日次勤怠を持つ
    public function attendanceRecords(): HasMany
    {
        $firstDay = Carbon::parse($this->month)->startOfMonth();
        $lastDay = Carbon::parse($this->month)->endOfMonth();

        return $this->hasMany(AttendanceRecord::class, 'user_id', 'user_id')
            ->whereBetween('date', [$firstDay, $lastDay]);
    }
}
