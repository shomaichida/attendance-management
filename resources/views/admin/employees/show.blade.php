<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">👤 社員詳細</h2>

      <a
        href="{{ route('admin.employees.index') }}"
        class="text-sm font-semibold text-blue-600 transition hover:text-blue-800"
      >
        社員一覧へ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
      <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
          <div>
            <p class="text-sm text-gray-500">社員番号</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $user->employee_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">氏名</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $user->name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">メールアドレス</p>
            <p class="mt-1 break-all font-semibold text-gray-900">{{ $user->email }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">部署</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $user->department ?? '未設定' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">有効状態</p>
            <p class="mt-1 font-semibold {{ $user->isActive() ? 'text-green-600' : 'text-gray-500' }}">
              {{ $user->isActive() ? '有効' : '無効' }}
            </p>
          </div>
        </div>
      </section>

      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">勤務日数</p>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ $monthlySummary->workingDays }}日</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">合計勤務時間</p>
          <p class="mt-2 text-2xl font-bold text-green-600">{{ $monthlySummary->totalWorkedTime }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">合計休憩時間</p>
          <p class="mt-2 text-2xl font-bold text-yellow-600">{{ $monthlySummary->totalBreakTime }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">平均勤務時間</p>
          <p class="mt-2 text-2xl font-bold text-blue-600">{{ $monthlySummary->averageWorkedTime }}</p>
        </div>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5">
          <div class="flex justify-end">
            <a
              href="{{ route('admin.employees.attendances.export', ['user' => $user, 'month' => $targetMonth->format('Y-m')]) }}"
              class="rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-green-700"
            >
              CSV出力
            </a>
          </div>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <a
            href="{{ route('admin.employees.show', ['user' => $user, 'month' => $previousMonth]) }}"
            class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
          >
            ← 前月
          </a>

          <h3 class="text-center text-xl font-bold text-gray-900">
            {{ $targetMonth->format('Y年n月') }}の勤怠
          </h3>

          <a
            href="{{ route('admin.employees.show', ['user' => $user, 'month' => $nextMonth]) }}"
            class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
          >
            翌月 →
          </a>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">日付</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">出勤</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">退勤</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">休憩時間</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">勤務時間</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">状態</th>
                <th class="whitespace-nowrap px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">詳細</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              @forelse ($attendances as $attendance)
                <tr class="hover:bg-gray-50">
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $attendance->work_date->format('Y/m/d') }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $attendance->clock_in?->format('H:i') ?? '-' }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $attendance->clock_out?->format('H:i') ?? '-' }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $attendance->breakTime() }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $attendance->workedTime() }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold {{ $attendance->statusColor() }}">{{ $attendance->status() }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <a
                      href="{{ route('admin.employees.attendances.show', [$user, $attendance]) }}"
                      class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                      詳細
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                    対象月の勤怠記録はありません。
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
