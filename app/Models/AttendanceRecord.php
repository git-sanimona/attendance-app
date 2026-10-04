<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_break_time',
        'total_time',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'total_break_time' => 'integer',
            'total_time' => 'integer',
        ];
    }

    // 一日の勤怠は1人のユーザーに紐づく
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 一日の勤怠は複数の休憩実績を持つ
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // 一日の勤怠に対して複数の修正申請が出される
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
