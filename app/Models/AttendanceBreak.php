<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];

    protected function casts(): array
    {
        return [
            'break_in' => 'datetime',
            'break_out' => 'datetime',
        ];
    }

    // 1つの休憩は1つの勤怠に紐づく
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
