<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'attendance_break_id',
        'new_break_in',
        'new_break_out',
    ];

    protected function casts(): array
    {
        return [
            'new_break_in' => 'datetime',
            'new_break_out' => 'datetime',
        ];
    }

    // 一つの休憩リクエストは1つの休憩実績に紐づく
    public function attendanceBreak(): BelongsTo
    {
        return $this->belongsTo(AttendanceBreak::class);
    }

    // 一つの休憩申請は一つの修正申請に紐づく
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
