<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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

                    $activeBreak = $breaks
                    ->first(fn ($break) => $break->break_end === null);

                    $totalBreakMinutes = $breaks->sum('break_minutes');

                    $workedMinutes = 0;

                    if ($todayAttendance?->clock_in) {
                    $workEnd = $todayAttendance->clock_out ?? now();

                    $workedMinutes = max(
                    0,
                    $todayAttendance->clock_in->diffInMinutes($workEnd)
                    - $totalBreakMinutes
                    );
                    }

                    $workedHours = intdiv($workedMinutes, 60);
                    $remainingWorkedMinutes = $workedMinutes % 60;
                    @endphp

                    <h3 class="mb-4 text-xl font-bold">本日の勤怠</h3>

                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                        <dl class="space-y-2">
                            <div class="flex justify-between border-b border-gray-200 pb-2">
                                <dt class="font-semibold">勤務日</dt>
                                <dd>{{ now()->format('Y/m/d') }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-200 pb-2">
                                <dt class="font-semibold">出勤</dt>
                                <dd>{{ $todayAttendance?->clock_in?->format('H:i') ?? '未打刻' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-200 pb-2">
                                <dt class="font-semibold">退勤</dt>
                                <dd>{{ $todayAttendance?->clock_out?->format('H:i') ?? '未打刻' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-200 pb-2">
                                <dt class="font-semibold">休憩状態</dt>
                                <dd>{{ $activeBreak ? '休憩中' : '勤務中' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-gray-200 pb-2">
                                <dt class="font-semibold">休憩時間</dt>
                                <dd>{{ $totalBreakMinutes }}分</dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="font-semibold">勤務時間</dt>
                                <dd>
                                    @if ($todayAttendance?->clock_in)
                                    {{ $workedHours }}時間{{ $remainingWorkedMinutes }}分
                                    @else
                                    未計算
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    @if (! $todayAttendance || ! $todayAttendance->clock_in)
                    <form method="POST" action="{{ route('attendance.clock-in') }}">
                        @csrf
                        <button
                            type="submit"
                            class="rounded bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                            出勤する
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
                            class="rounded bg-yellow-600 px-6 py-3 font-semibold text-white hover:bg-yellow-700">
                            休憩を終了する
                        </button>
                    </form>
                    @else
                    <div class="flex flex-wrap gap-4">
                        <form method="POST" action="{{ route('attendance.break-start') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700">
                                休憩を開始する
                            </button>
                        </form>

                        <form method="POST" action="{{ route('attendance.clock-out') }}">
                            @csrf
                            <button
                                type="submit"
                                class="rounded bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-700">
                                退勤する
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($breaks->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="mb-3 text-xl font-bold">本日の休憩履歴</h3>

                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
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
</x-app-layout>