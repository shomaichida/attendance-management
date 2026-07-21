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

                @if ($latestCorrectionRequest)
                    <div class="mb-6 rounded-lg border px-4 py-3 font-semibold {{ $latestCorrectionRequest->isPending() ? 'border-yellow-200 bg-yellow-50 text-yellow-700' : 'border-green-200 bg-green-50 text-green-700' }}">
                        最新の修正申請：{{ $latestCorrectionRequest->statusLabel() }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        入力内容を確認してください。
                        <x-input-error :messages="$errors->get('attendance')" class="mt-2" />
                    </div>
                @endif

                <form action="{{ route('attendance-correction-requests.store', $attendance) }}" method="POST">
                    @csrf

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

                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold">休憩</h3>
                        <button id="add-break" type="button" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">休憩を追加</button>
                    </div>

                    <div id="break-rows" class="mt-4 space-y-4">
                        @foreach ($breakRows as $index => $break)
                            <div class="break-row rounded-lg bg-gray-100 p-4">
                                <div class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
                                    <div>
                                        <label class="mb-2 block font-semibold">休憩開始</label>
                                        <input type="time" name="breaks[{{ $index }}][break_start]" value="{{ $break['break_start'] ?? '' }}" class="w-full rounded-md border-gray-300">
                                        <x-input-error :messages="$errors->get('breaks.'.$index.'.break_start')" class="mt-2" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block font-semibold">休憩終了</label>
                                        <input type="time" name="breaks[{{ $index }}][break_end]" value="{{ $break['break_end'] ?? '' }}" class="w-full rounded-md border-gray-300">
                                        <x-input-error :messages="$errors->get('breaks.'.$index.'.break_end')" class="mt-2" />
                                    </div>
                                    <button type="button" class="remove-break rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 sm:mt-8">削除</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <label for="reason" class="mb-2 block font-semibold">申請理由</label>
                        <textarea id="reason" name="reason" rows="4" class="w-full rounded-md border-gray-300">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button
                            type="submit"
                            class="rounded-md bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">
                            修正申請を送信
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

    <template id="break-row-template">
        <div class="break-row rounded-lg bg-gray-100 p-4">
            <div class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
                <div><label class="mb-2 block font-semibold">休憩開始</label><input type="time" name="breaks[__INDEX__][break_start]" class="w-full rounded-md border-gray-300"></div>
                <div><label class="mb-2 block font-semibold">休憩終了</label><input type="time" name="breaks[__INDEX__][break_end]" class="w-full rounded-md border-gray-300"></div>
                <button type="button" class="remove-break rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 sm:mt-8">削除</button>
            </div>
        </div>
    </template>

    <script>
        (() => {
            const rows = document.getElementById('break-rows');
            const template = document.getElementById('break-row-template');
            let nextIndex = {{ count($breakRows) }};

            document.getElementById('add-break').addEventListener('click', () => {
                rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
            });

            rows.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-break');

                if (button) {
                    button.closest('.break-row').remove();
                }
            });
        })();
    </script>
</x-app-layout>
