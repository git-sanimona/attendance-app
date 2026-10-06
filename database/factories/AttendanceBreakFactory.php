<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceBreak>
 */
class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //休憩開始時刻(デフォルト)12:00。日付はテスト側(作成時)に記述して指定。
        $breakIn = Carbon::parse('12:00:00');
        $breakOut = $breakIn->copy()->addMinutes(60);

        return [
            'attendance_record_id' => AttendanceRecord::factory(),
            'break_in' => $breakIn,
            'break_out' => $breakOut,
        ];
    }

    //2回目の小休憩(テストで回数をランダムにして使用予定)
    public function shortBreak(): static
    {
        return $this->state(fn(array $attributes) => [
            'break_in' => Carbon::parse('15:00:00'),
            'break_out' => Carbon::parse('15:15:00'),
        ]);
    }

    //休憩中の状態を生成するStateメソッド
    public function breaking(): static
    {
        return $this->state(fn(array $attributes) => [
            'break_out' => null,
        ]);
    }
}
