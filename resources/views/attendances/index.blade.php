<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a
                        href="{{ route('attendances.index', ['month' => $previousMonth]) }}"
                        class="rounded-md bg-gray-200 px-4 py-2 text-center font-semibold text-gray-700 hover:bg-gray-300">
                        &lt; 前月
                    </a>

                    <h3 class="text-center text-2xl font-bold">
                        {{ $targetMonth->format('Y年n月') }}
                    </h3>

                    <a
                        href="{{ route('attendances.index', ['month' => $nextMonth]) }}"
                        class="rounded-md bg-gray-200 px-4 py-2 text-center font-semibold text-gray-700 hover:bg-gray-300">
                        翌月 &gt;
                    </a>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full whitespace-nowrap">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border-b px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                    日付
                                </th>
                                <th class="border-b px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    出勤
                                </th>
                                <th class="border-b px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    退勤
                                </th>
                                <th class="border-b px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    休憩
                                </th>
                                <th class="border-b px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    勤務時間
                                </th>
                                <th class="border-b px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    操作
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($attendances as $attendance)
                            @php
                            $workedMinutes = $attendance->workedMinutes();
                            $workedHours = intdiv($workedMinutes, 60);
                            $remainingMinutes = $workedMinutes % 60;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $attendance->work_date->format('n/j') }}
                                </td>

                                <td class="px-4 py-3 text-center text-gray-700">
                                    {{ $attendance->clock_in?->format('H:i') ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center text-gray-700">
                                    {{ $attendance->clock_out?->format('H:i') ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center text-gray-700">
                                    {{ $attendance->totalBreakMinutes() }}分
                                </td>

                                <td class="px-4 py-3 text-center text-gray-700">
                                    @if ($attendance->clock_in)
                                    {{ $workedHours }}時間{{ $remainingMinutes }}分
                                    @else
                                    -
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <a
                                        href="{{ route('attendances.show', ['attendance' => $attendance->id]) }}"
                                        class="inline-block rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    この月の勤怠データはありません。
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                $totalWorkedHours = intdiv($totalWorkedMinutes, 60);
                $remainingTotalWorkedMinutes = $totalWorkedMinutes % 60;
                $totalBreakHours = intdiv($totalBreakMinutes, 60);
                $remainingTotalBreakMinutes = $totalBreakMinutes % 60;
                @endphp

                <div class="mt-6 rounded-lg bg-gray-50 p-6">
                    <h3 class="mb-4 text-xl font-bold text-gray-900">
                        今月の集計
                    </h3>

                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-4 shadow-sm">
                            <dt class="text-sm font-semibold text-gray-500">
                                勤務日数
                            </dt>
                            <dd class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $workingDays }}日
                            </dd>
                        </div>

                        <div class="rounded-lg bg-white p-4 shadow-sm">
                            <dt class="text-sm font-semibold text-gray-500">
                                合計勤務時間
                            </dt>
                            <dd class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $totalWorkedHours }}時間{{ $remainingTotalWorkedMinutes }}分
                            </dd>
                        </div>

                        <div class="rounded-lg bg-white p-4 shadow-sm">
                            <dt class="text-sm font-semibold text-gray-500">
                                合計休憩時間
                            </dt>
                            <dd class="mt-2 text-2xl font-bold text-gray-900">
                                {{ $totalBreakHours }}時間{{ $remainingTotalBreakMinutes }}分
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>