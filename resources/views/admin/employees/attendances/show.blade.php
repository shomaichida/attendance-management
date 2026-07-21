<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">📋 勤怠詳細</h2>
      <a href="{{ route('admin.employees.show', ['user' => $user, 'month' => $attendance->work_date->format('Y-m')]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
        社員詳細へ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
      @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
      @endif

      <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div><p class="text-sm text-gray-500">社員番号</p><p class="mt-1 font-semibold text-gray-900">{{ $user->employee_number }}</p></div>
          <div><p class="text-sm text-gray-500">氏名</p><p class="mt-1 font-semibold text-gray-900">{{ $user->name }}</p></div>
          <div><p class="text-sm text-gray-500">勤務日</p><p class="mt-1 font-semibold text-gray-900">{{ $attendance->work_date->format('Y年m月d日') }}</p></div>
          <div><p class="text-sm text-gray-500">出勤</p><p class="mt-1 font-semibold text-gray-900">{{ $attendance->clock_in?->format('H:i') ?? '-' }}</p></div>
          <div><p class="text-sm text-gray-500">退勤</p><p class="mt-1 font-semibold text-gray-900">{{ $attendance->clock_out?->format('H:i') ?? '-' }}</p></div>
          <div><p class="text-sm text-gray-500">勤務状態</p><p class="mt-1 font-semibold {{ $attendance->statusColor() }}">{{ $attendance->status() }}</p></div>
          <div><p class="text-sm text-gray-500">休憩時間</p><p class="mt-1 font-semibold text-gray-900">{{ $attendance->breakTime() }}</p></div>
          <div><p class="text-sm text-gray-500">勤務時間</p><p class="mt-1 font-semibold text-gray-900">{{ $attendance->workedTime() }}</p></div>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-6">
          <p class="text-sm text-gray-500">コメント</p>
          <p class="mt-2 whitespace-pre-wrap text-gray-900">{{ $attendance->memo ?? 'コメントはありません。' }}</p>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-6">
          <h3 class="text-lg font-bold text-gray-900">休憩履歴</h3>
          <div class="mt-4 space-y-3">
            @forelse ($attendance->breaks as $break)
              <div class="grid gap-2 rounded-lg bg-gray-50 p-4 text-sm sm:grid-cols-3">
                <p><span class="text-gray-500">開始：</span><span class="font-semibold text-gray-900">{{ $break->break_start->format('H:i') }}</span></p>
                <p><span class="text-gray-500">終了：</span><span class="font-semibold text-gray-900">{{ $break->break_end?->format('H:i') ?? '-' }}</span></p>
                <p><span class="text-gray-500">休憩時間：</span><span class="font-semibold text-gray-900">{{ $break->break_minutes }}分</span></p>
              </div>
            @empty
              <p class="text-sm text-gray-500">休憩履歴はありません</p>
            @endforelse
          </div>
        </div>

        <div class="mt-8">
          <a href="{{ route('admin.employees.attendances.edit', [$user, $attendance]) }}" class="touch-target w-full rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white transition hover:bg-blue-700 sm:w-auto">
            編集する
          </a>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
