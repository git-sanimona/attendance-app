<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    // 承認ステータスの定数定義(承認待ち:0、承認済み:1)
    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    protected $fillable = [
        'user_id',
        'attendance_record_id',
        'approval_status',
        'new_date',
        'new_clock_in',
        'new_clock_out',
        'comment',
        'application_date',
    ];

    protected function casts(): array
    {
        return [
            'approval_status' => 'integer',
            'new_date' => 'date',
            'new_clock_in' => 'datetime',
            'new_clock_out' => 'datetime',
            'application_date' => 'datetime',
        ];
    }

    // 承認ステータスのアクセサ。ステータスを日本語文字列にしてBladeに渡す。
    protected function approvalStatus(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match ($value) {
                self::STATUS_PENDING => '承認待ち',
                self::STATUS_APPROVED => '承認済み',
                default => null,
            }
        );
    }

    // 一つの修正申請は1人のユーザーに紐づく
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 一つの修正申請は一つの勤怠に紐づく
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    // 一つの修正申請は複数回の休憩修正を持つ
    public function proposalBreaks(): HasMany
    {
        return $this->hasMany(ProposalBreak::class);
    }
}
