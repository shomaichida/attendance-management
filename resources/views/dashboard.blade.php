<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                    👋 勤怠ダッシュボード
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    おはようございます、{{ Auth::user()->name }} さん！
                </p>
            </div>

            <div class="text-left sm:text-right">
                <p class="text-sm text-gray-500">
                    {{ now()->isoFormat('YYYY年M月D日 (ddd)') }}
                </p>

                <p id="current-time"
                    class="text-3xl font-bold text-blue-600">
                    {{ now()->format('H:i:s') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-4 text-gray-900 sm:p-6">
                    @if (session('success'))
                    <div class="mb-4 rounded bg-green-100 p-3 text-green-700">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-4 rounded bg-red-100 p-3 text-red-700">
                        {{ session('error') }}
                    </div>
                    @endif

                    @php
                    $breaks = $todayAttendance?->breaks ?? collect();

                    $activeBreak = $breaks->first(
                    fn ($break) => $break->break_end === null
                    );
                    @endphp

                    <h3 class="mb-6 text-2xl font-bold text-gray-800">
                        📊 本日の勤怠
                    </h3>

                    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                📅 勤務日
                            </dt>

                            <dd class="mt-2 text-2xl font-bold text-gray-800">
                                {{ now()->format('Y/m/d') }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                ⏰ 出勤
                            </dt>

                            <dd class="mt-2 text-2xl font-bold text-green-600">
                                {{ $todayAttendance?->clock_in?->format('H:i') ?? '未打刻' }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                🏁 退勤
                            </dt>

                            <dd class="mt-2 text-2xl font-bold text-red-600">
                                {{ $todayAttendance?->clock_out?->format('H:i') ?? '未打刻' }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                ☕ 休憩状態
                            </dt>

                            <dd class="mt-2 text-2xl font-bold {{ $todayAttendance?->statusColor() ?? 'text-gray-500' }}">
                                {{ $todayAttendance?->status() ?? '未出勤' }}
                            </dd>

                        </div>

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                ☕ 休憩時間
                            </dt>

                            <dd class="mt-2 text-2xl font-bold text-blue-600">
                                {{ $todayAttendance?->breakTime() ?? '0時間0分' }}
                            </dd>
                        </div>

                        <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                            <dt class="text-sm font-semibold text-gray-500">
                                💼 勤務時間
                            </dt>

                            <dd class="mt-2 text-2xl font-bold text-indigo-600">
                                {{ $todayAttendance?->workedTime() ?? '未計算' }}
                            </dd>
                        </div>

                    </div>

                    @if (! $todayAttendance || ! $todayAttendance->clock_in)
                    <form method="POST" action="{{ route('attendance.clock-in') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-blue-600 px-6 py-4 text-lg font-bold text-white shadow-md transition hover:bg-blue-700 hover:shadow-lg">
                            🟢 出勤する
                        </button>
                    </form>
                    @elseif ($todayAttendance->clock_out)
                    <div class="rounded bg-gray-100 p-4 font-semibold text-gray-700">
                        本日の勤務は終了しました。
                    </div>
                    @elseif ($activeBreak)
                    <form method="POST" action="{{ route('attendance.break-end') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-yellow-500 px-6 py-4 text-lg font-bold text-white shadow-md transition hover:bg-yellow-600 hover:shadow-lg">
                            休憩を終了する
                        </button>
                    </form>
                    @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        <form method="POST" action="{{ route('attendance.break-start') }}" class="w-full">
                            @csrf
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-green-600 px-6 py-4 text-lg font-bold text-white shadow-md transition hover:bg-green-700 hover:shadow-lg">
                                休憩を開始する
                            </button>
                        </form>

                        <form method="POST" action="{{ route('attendance.clock-out') }}" class="w-full">
                            @csrf
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-red-600 px-6 py-4 text-lg font-bold text-white shadow-md transition hover:bg-red-700 hover:shadow-lg">
                                退勤する
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($breaks->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="mb-3 text-xl font-bold">本日の休憩履歴</h3>

                        <p class="mobile-scroll-hint">横にスクロールして休憩履歴を確認できます →</p>
                        <div class="table-scroll rounded-lg border border-gray-200">
                            <table class="min-w-[560px] divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">回数</th>
                                        <th class="px-4 py-2 text-left">開始</th>
                                        <th class="px-4 py-2 text-left">終了</th>
                                        <th class="px-4 py-2 text-left">休憩時間</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($breaks as $break)
                                    <tr>
                                        <td class="px-4 py-2">{{ $loop->iteration }}回目</td>

                                        <td class="px-4 py-2">
                                            {{ $break->break_start->format('H:i') }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $break->break_end?->format('H:i') ?? '休憩中' }}
                                        </td>

                                        <td class="px-4 py-2">
                                            @if ($break->break_end)
                                            {{ $break->break_minutes }}分
                                            @else
                                            計測中
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateClock() {
            const now = new Date();

            const time =
                now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0') + ':' +
                now.getSeconds().toString().padStart(2, '0');

            document.getElementById('current-time').textContent = time;
        }

        updateClock();
        setInterval(updateClock, 1000);
    </script>
</x-app-layout>
