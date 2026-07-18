<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">
        👨‍💼 管理者ダッシュボード
      </h2>

      <a
        href="{{ route('dashboard') }}"
        class="text-sm font-semibold text-blue-600 transition hover:text-blue-800">
        社員画面へ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">
            出勤中
          </p>

          <p class="mt-2 text-3xl font-bold text-green-600">
            {{ $workingCount }}人
          </p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">
            休憩中
          </p>

          <p class="mt-2 text-3xl font-bold text-yellow-600">
            {{ $onBreakCount }}人
          </p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">
            退勤済
          </p>

          <p class="mt-2 text-3xl font-bold text-blue-600">
            {{ $finishedCount }}人
          </p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
          <p class="text-sm text-gray-500">
            登録社員数
          </p>

          <p class="mt-2 text-3xl font-bold text-gray-800">
            {{ $employeeCount }}人
          </p>
        </div>

      </div>

      <div class="mt-8 rounded-xl border border-gray-100 bg-white p-8 shadow-sm">
        <h3 class="text-xl font-bold text-gray-900">
          管理メニュー
        </h3>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          <a
            href="{{ route('admin.employees.index') }}"
            class="rounded-lg bg-blue-600 px-6 py-4 text-center font-semibold text-white transition hover:bg-blue-700">
            👥 社員一覧
          </a>

          <a
            href="#"
            class="rounded-lg bg-green-600 px-6 py-4 text-center font-semibold text-white transition hover:bg-green-700">
            📅 勤怠一覧
          </a>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
