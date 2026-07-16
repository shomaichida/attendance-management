<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6 flex items-center justify-between">
                    <a
                        href="{{ route('attendances.index', ['month' => $previousMonth]) }}"
                        class="rounded-md bg-gray-200 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-300"
                    >
                        &lt; 前月
                    </a>

                    <h3 class="text-2xl font-bold">
                        {{ $targetMonth->format('Y年n月') }}
                    </h3>

                    <a
                        href="{{ route('attendances.index', ['month' => $nextMonth]) }}"
                        class="rounded-md bg-gray-200 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-300"
                    >
                        翌月 &gt;
                    </a>
                </div>

                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">日付</th>
                            <th class="border px-4 py-2">出勤</th>
                            <th class="border px-4 py-2">退勤</th>
                            <th class="border px-4 py-2">休憩</th>
                            <th class="border px-4 py-2">勤務時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            @php
                                $workedMinutes = $attendance->workedMinutes();
                                $workedHours = intdiv($workedMinutes, 60);
                                $remainingMinutes = $workedMinutes % 60;
                            @endphp
                            <tr>
                                <td class="border px-4 py-2">{{ $attendance->work_date->format('n/j') }}</td>
                                <td class="border px-4 py-2">{{ $attendance->clock_in?->format('H:i') ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $attendance->clock_out?->format('H:i') ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $attendance->totalBreakMinutes() }}分</td>
                                <td class="border px-4 py-2">
                                    @if ($attendance->clock_in)
                                        {{ $workedHours }}時間{{ $remainingMinutes }}分
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border px-4 py-6 text-center text-gray-500">
                                    この月の勤怠データはありません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>