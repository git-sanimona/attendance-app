<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * 退勤打刻処理（労働時間・休憩時間を計算して保存）
     */
    public function clockOut(AttendanceRecord $attendance, ?Carbon $clockOutTime = null): AttendanceRecord
    {
        return DB::transaction(function () use ($attendance, $clockOutTime) {
            $attendance->clock_out = $clockOutTime ?? now();
            $attendance->load('breaks');

            return $this->calculateTimeSave($attendance);
        });
    }

    // 紐づく休憩レコードから合計休憩時間を算出
    public function calculateTotalBreakTime(AttendanceRecord $attendance): int
    {
        return $attendance->breaks->sum(function ($break) {
            if ($break->break_in && $break->break_out) {
                return $break->break_in->diffInMinutes($break->break_out);
            }

            return 0;
        });
    }

    // 出退勤時刻と合計休憩時間から実働時間を算出
    public function calculateTotalTime(AttendanceRecord $attendance, int $totalBreakTime): ?int
    {
        if (! $attendance->clock_in || ! $attendance->clock_out) {
            return null;
        }

        $grossMinutes = $attendance->clock_in->diffInMinutes($attendance->clock_out);

        return max(0, $grossMinutes - $totalBreakTime);
    }

    // 勤怠の合計休憩時間と実労働時間の保存
    public function calculateTimeSave(AttendanceRecord $attendance): AttendanceRecord
    {
        $totalBreakTime = $this->calculateTotalBreakTime($attendance);

        $totalTime = $this->calculateTotalTime($attendance, $totalBreakTime);

        $attendance->fill([
            'total_break_time' => $totalBreakTime,
            'total_time' => $totalTime,
        ]);

        $attendance->save();

        return $attendance;
    }
}
