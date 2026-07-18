<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            勤怠詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-100 border border-green-400 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('attendances.update', $attendance) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-5">
                        <label class="block font-semibold mb-2">
                            勤務日
                        </label>

                        <input
                            type="text"
                            value="{{ $attendance->work_date->format('Y年m月d日') }}"
                            class="w-full rounded-md border-gray-300 bg-gray-100"
                            readonly>
                    </div>

                    <div class="mb-5">
                        <label class="block font-semibold mb-2">
                            出勤時刻
                        </label>

                        <input
                            type="time"
                            name="clock_in"
                            value="{{ optional($attendance->clock_in)->format('H:i') }}"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="mb-5">
                        <label class="block font-semibold mb-2">
                            退勤時刻
                        </label>

                        <input
                            type="time"
                            name="clock_out"
                            value="{{ optional($attendance->clock_out)->format('H:i') }}"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <hr class="my-6">

                    <h3 class="text-lg font-bold mb-4">
                        休憩一覧
                    </h3>

                    @forelse ($attendance->breaks as $break)
                        <div class="mb-3 rounded-lg bg-gray-100 p-4">
                            {{ $break->break_start?->format('H:i') ?? '-' }}
                            ～
                            {{ $break->break_end?->format('H:i') ?? '休憩中' }}
                        </div>
                    @empty
                        <p class="text-gray-500">
                            休憩記録はありません。
                        </p>
                    @endforelse

                    <div class="mt-8 flex gap-3">
                        <button
                            type="submit"
                            class="rounded-md bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">
                            保存
                        </button>

                        <a
                            href="{{ route('attendances.index', ['month' => $attendance->work_date->format('Y-m')]) }}"
                            class="rounded-md bg-gray-600 px-5 py-2 text-white hover:bg-gray-700">
                            一覧へ戻る
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>