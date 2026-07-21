<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">
        👥 社員一覧
      </h2>

      <a
        href="{{ route('admin.dashboard') }}"
        class="text-sm font-semibold text-blue-600 transition hover:text-blue-800">
        管理者ダッシュボードへ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5">
          <h3 class="text-lg font-bold text-gray-900">登録社員</h3>
          <p class="mt-1 text-sm text-gray-500">現在有効な社員の勤務状態を確認できます。</p>
        </div>

        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
          @forelse ($employees as $employee)
            <article class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                    {{ $employee->employee_number }}
                  </p>
                  <h4 class="mt-1 truncate text-lg font-bold text-gray-900">
                    {{ $employee->name }}
                  </h4>
                  <p class="mt-1 truncate text-sm text-gray-500">
                    {{ $employee->department ?? '部署未設定' }}
                  </p>
                </div>

                <span class="shrink-0 rounded-full bg-gray-50 px-3 py-1 text-sm font-semibold {{ $employee->todayAttendance->statusColor() }}">
                  {{ $employee->todayAttendance->status() }}
                </span>
              </div>

              <a
                href="{{ route('admin.employees.show', $employee) }}"
                class="touch-target mt-6 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700"
              >
                詳細
              </a>
            </article>
          @empty
            <div class="py-12 text-center sm:col-span-2 lg:col-span-3">
              <p class="text-sm text-gray-500">表示できる社員はいません。</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
