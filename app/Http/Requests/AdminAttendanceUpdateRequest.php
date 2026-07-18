<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i', 'required_with:clock_out'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.id' => ['nullable', 'integer'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i', 'required_with:breaks.*.break_end'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'required_with:breaks.*.break_start', 'after_or_equal:breaks.*.break_start'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clock_in.date_format' => '出勤は時刻形式で入力してください。',
            'clock_in.required_with' => '退勤を入力する場合は出勤も入力してください。',
            'clock_out.date_format' => '退勤は時刻形式で入力してください。',
            'clock_out.after_or_equal' => '退勤は出勤以降の時刻にしてください。',
            'breaks.array' => '休憩情報の形式が正しくありません。',
            'breaks.*.id.integer' => '休憩IDの形式が正しくありません。',
            'breaks.*.break_start.date_format' => '休憩開始は時刻形式で入力してください。',
            'breaks.*.break_start.required_with' => '休憩終了を入力する場合は休憩開始も入力してください。',
            'breaks.*.break_end.date_format' => '休憩終了は時刻形式で入力してください。',
            'breaks.*.break_end.required_with' => '休憩開始を入力する場合は休憩終了も入力してください。',
            'breaks.*.break_end.after_or_equal' => '休憩終了は休憩開始以降の時刻にしてください。',
            'memo.max' => 'コメントは1000文字以内で入力してください。',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $clockIn = $this->minutes($this->input('clock_in'));
                $clockOut = $this->minutes($this->input('clock_out'));
                $breaks = collect($this->input('breaks', []))
                    ->map(fn (array $break, $index): array => [
                        'index' => $index,
                        'start' => $this->minutes($break['break_start'] ?? null),
                        'end' => $this->minutes($break['break_end'] ?? null),
                    ])
                    ->filter(fn (array $break): bool => $break['start'] !== null || $break['end'] !== null)
                    ->values();

                foreach ($breaks as $break) {
                    if ($clockIn !== null && $break['start'] < $clockIn) {
                        $validator->errors()->add(
                            "breaks.{$break['index']}.break_start",
                            '休憩開始は出勤以降の時刻にしてください。',
                        );
                    }

                    if ($clockOut !== null && $break['end'] > $clockOut) {
                        $validator->errors()->add(
                            "breaks.{$break['index']}.break_end",
                            '休憩終了は退勤以前の時刻にしてください。',
                        );
                    }
                }

                $sortedBreaks = $breaks->sortBy('start')->values();

                for ($index = 1; $index < $sortedBreaks->count(); $index++) {
                    $previous = $sortedBreaks[$index - 1];
                    $current = $sortedBreaks[$index];

                    if ($current['start'] < $previous['end']) {
                        $validator->errors()->add(
                            "breaks.{$current['index']}.break_start",
                            '休憩時間が他の休憩と重複しています。',
                        );
                    }
                }
            },
        ];
    }

    private function minutes(?string $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
