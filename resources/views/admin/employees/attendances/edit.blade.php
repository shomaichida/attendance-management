<x-app-layout>
  <x-slot name="header">
    <h2 class="text-2xl font-bold text-gray-900">✏️ 勤怠編集</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <form action="{{ route('admin.employees.attendances.update', [$user, $attendance]) }}" method="POST" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        @csrf
        @method('PUT')

        <div class="mb-6 rounded-lg bg-gray-50 p-4">
          <p class="font-semibold text-gray-900">{{ $user->employee_number }} / {{ $user->name }}</p>
          <p class="mt-1 text-sm text-gray-500">{{ $attendance->work_date->format('Y年m月d日') }}</p>
        </div>

        @if ($errors->any())
          <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            入力内容を確認してください。
          </div>
        @endif

        <div class="grid gap-6 sm:grid-cols-2">
          <div>
            <label for="clock_in" class="mb-2 block text-sm font-semibold text-gray-700">出勤</label>
            <input id="clock_in" name="clock_in" type="time" value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('clock_in')" class="mt-2" />
          </div>
          <div>
            <label for="clock_out" class="mb-2 block text-sm font-semibold text-gray-700">退勤</label>
            <input id="clock_out" name="clock_out" type="time" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <x-input-error :messages="$errors->get('clock_out')" class="mt-2" />
          </div>
        </div>

        <div class="mt-8 border-t border-gray-100 pt-6">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-bold text-gray-900">休憩</h3>
            <button id="add-break" type="button" class="touch-target w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto">
              休憩を追加
            </button>
          </div>

          <div id="break-rows" class="mt-4 space-y-4">
            @foreach ($breakRows as $index => $break)
              <div class="break-row rounded-xl border border-gray-200 bg-gray-50 p-4">
                <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break['id'] ?? '' }}">
                <div class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start">
                  <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">休憩開始</label>
                    <input name="breaks[{{ $index }}][break_start]" type="time" value="{{ $break['break_start'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('breaks.'.$index.'.break_start')" class="mt-2" />
                  </div>
                  <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">休憩終了</label>
                    <input name="breaks[{{ $index }}][break_end]" type="time" value="{{ $break['break_end'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <x-input-error :messages="$errors->get('breaks.'.$index.'.break_end')" class="mt-2" />
                    <x-input-error :messages="$errors->get('breaks.'.$index.'.id')" class="mt-2" />
                  </div>
                  <button type="button" class="remove-break touch-target w-full rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 sm:mt-7 sm:w-auto">
                    削除
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="mt-6">
          <label for="memo" class="mb-2 block text-sm font-semibold text-gray-700">コメント</label>
          <textarea id="memo" name="memo" rows="5" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('memo', $attendance->memo) }}</textarea>
          <x-input-error :messages="$errors->get('memo')" class="mt-2" />
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
          <button type="submit" class="touch-target w-full rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white transition hover:bg-blue-700 sm:w-auto">保存する</button>
          <a href="{{ route('admin.employees.attendances.show', [$user, $attendance]) }}" class="touch-target w-full rounded-lg bg-gray-100 px-5 py-2.5 text-center font-semibold text-gray-700 transition hover:bg-gray-200 sm:w-auto">キャンセル</a>
        </div>
      </form>
    </div>
  </div>

  <template id="break-row-template">
    <div class="break-row rounded-xl border border-gray-200 bg-gray-50 p-4">
      <input type="hidden" name="breaks[__INDEX__][id]" value="">
      <div class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start">
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">休憩開始</label>
          <input name="breaks[__INDEX__][break_start]" type="time" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700">休憩終了</label>
          <input name="breaks[__INDEX__][break_end]" type="time" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <button type="button" class="remove-break touch-target w-full rounded-lg bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 sm:mt-7 sm:w-auto">
          削除
        </button>
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
