<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 過去１ヶ月以内のランダムな出勤日
        $date = Carbon::instance(fake()->dateTimeBetween('-1 month', 'now'))->format('Y-m-d');

        // 定時付近(8:45~9:15)の出勤時間
        $clockIn = Carbon::parse("{$date}".fake()->dateTimeBetween("{$date}08:45:00", "{$date}9:15:00"));

        // 定時付近(17:30~19:00)の退勤時間
        $clockOut = Carbon::parse("{$date}".fake()->dateTimeBetween("{$date}17:30:00", "{$date}19:00:00"));

        // 休憩時間(デフォルト)
        $defaultBreakTime = 60;

        // 労働時間
        $totalTime = $clockOut->diffInMinutes($clockIn) - $defaultBreakTime;

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_break_time' => $defaultBreakTime,
            'total_time' => $totalTime,
            'comment' => fake()->optional(0.2)->realText(30),
        ];
    }

    // 勤務中のStateメソッド
    public function working(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_out' => null,
            'total_break_time' => 0,
            'total_time' => null,
        ]);
    }

    // 勤怠データの生成後に休憩レコードが連動して生成。休憩時間と労働時間を同期、再計算する。
    public function configure(): static
    {
        return $this->afterCreating(function (AttendanceRecord $attendance) {

            // まず休憩データがあるか確認
            if ($attendance->breaks->isEmpty()) {
                return;
            }

            // 各休憩レコードの合算
            $totalBreakTime = $attendance->breaks->sum(
                fn ($break) => $break->break_out?->diffInMinutes($break->break_in) ?? 0
            );

            // 労働時間を計算する。(勤務中(退勤していない)ならnull)
            $totalTime = $attendance->clock_out
                ? $attendance->clock_out->diffInMinutes($attendance->clock_in) - $totalBreakTime
                : null;

            // 更新
            $attendance->update([
                'total_break_time' => $totalBreakTime,
                'total_time' => $totalTime,
            ]);
        });
    }
}
