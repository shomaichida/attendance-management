<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * 一般ユーザーの勤怠修正申請を検証するFormRequest。
 */
class AttendanceCorrectionStoreRequest extends FormRequest
{
    /**
     * 対象勤怠がログインユーザー本人のものか確認する。
     */
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->route('attendance')?->user_id === $this->user()->id;
    }

    /**
     * 修正後の出退勤・複数休憩・申請理由の検証ルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i', 'required_with:clock_out'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i', 'required_with:breaks.*.break_end'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'required_with:breaks.*.break_start', 'after_or_equal:breaks.*.break_start'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * 勤怠修正申請で使用する日本語バリデーションメッセージを返す。
     *
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
            'breaks.*.break_start.date_format' => '休憩開始は時刻形式で入力してください。',
            'breaks.*.break_start.required_with' => '休憩終了を入力する場合は休憩開始も入力してください。',
            'breaks.*.break_end.date_format' => '休憩終了は時刻形式で入力してください。',
            'breaks.*.break_end.required_with' => '休憩開始を入力する場合は休憩終了も入力してください。',
            'breaks.*.break_end.after_or_equal' => '休憩終了は休憩開始以降の時刻にしてください。',
            'reason.required' => '申請理由を入力してください。',
            'reason.max' => '申請理由は1000文字以内で入力してください。',
        ];
    }

    /**
     * 休憩が勤務時間内に収まり、互いに重複しないことを検証する。
     *
     * @return array<int, callable(Validator): void>
     */
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
                    ->filter(fn (array $break): bool => $break['start'] !== null)
                    ->sortBy('start')
                    ->values();

                foreach ($breaks as $index => $break) {
                    if ($clockIn !== null && $break['start'] < $clockIn) {
                        $validator->errors()->add("breaks.{$break['index']}.break_start", '休憩開始は出勤以降の時刻にしてください。');
                    }

                    if ($clockOut !== null && $break['end'] > $clockOut) {
                        $validator->errors()->add("breaks.{$break['index']}.break_end", '休憩終了は退勤以前の時刻にしてください。');
                    }

                    if ($index > 0 && $break['start'] < $breaks[$index - 1]['end']) {
                        $validator->errors()->add("breaks.{$break['index']}.break_start", '休憩時間が他の休憩と重複しています。');
                    }
                }
            },
        ];
    }

    private function minutes(?string $time): ?int
    {
        if (blank($time)) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
