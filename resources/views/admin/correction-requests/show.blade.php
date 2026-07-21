<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">📄 修正申請詳細</h2>
      <a href="{{ route('admin.correction-requests.index', ['status' => $correctionRequest->status]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
        申請一覧へ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
      @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
      @endif

      <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div><p class="text-sm text-gray-500">申請者名</p><p class="mt-1 font-semibold text-gray-900">{{ $correctionRequest->user->name }}</p></div>
          <div><p class="text-sm text-gray-500">対象日</p><p class="mt-1 font-semibold text-gray-900">{{ $correctionRequest->attendance->work_date->format('Y/m/d') }}</p></div>
          <div><p class="text-sm text-gray-500">申請日時</p><p class="mt-1 font-semibold text-gray-900">{{ $correctionRequest->created_at->format('Y/m/d H:i') }}</p></div>
          <div><p class="text-sm text-gray-500">承認状態</p><p class="mt-1 font-semibold {{ $correctionRequest->statusColor() }}">{{ $correctionRequest->statusLabel() }}</p></div>
        </div>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4"><h3 class="text-lg font-bold text-gray-900">出退勤の変更</h3></div>
        <div class="grid gap-6 p-4 sm:grid-cols-2 sm:p-6">
          <div class="rounded-lg bg-gray-50 p-4">
            <p class="font-semibold text-gray-700">修正前</p>
            <p class="mt-3 text-sm text-gray-600">出勤：{{ $correctionRequest->original_clock_in?->format('H:i') ?? '-' }}</p>
            <p class="mt-2 text-sm text-gray-600">退勤：{{ $correctionRequest->original_clock_out?->format('H:i') ?? '-' }}</p>
          </div>
          <div class="rounded-lg bg-blue-50 p-4">
            <p class="font-semibold text-blue-700">修正後</p>
            <p class="mt-3 text-sm text-gray-700">出勤：{{ $correctionRequest->requested_clock_in?->format('H:i') ?? '-' }}</p>
            <p class="mt-2 text-sm text-gray-700">退勤：{{ $correctionRequest->requested_clock_out?->format('H:i') ?? '-' }}</p>
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4"><h3 class="text-lg font-bold text-gray-900">休憩時間の変更</h3></div>
        <div class="grid gap-6 p-4 lg:grid-cols-2 lg:p-6">
          <div>
            <h4 class="mb-3 font-semibold text-gray-700">修正前</h4>
            <div class="space-y-3">
              @forelse ($originalBreaks as $break)
                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">{{ $break['start'] }} ～ {{ $break['end'] }}（{{ $break['duration'] }}）</div>
              @empty
                <p class="text-sm text-gray-500">休憩履歴はありません。</p>
              @endforelse
            </div>
          </div>
          <div>
            <h4 class="mb-3 font-semibold text-blue-700">修正後</h4>
            <div class="space-y-3">
              @forelse ($requestedBreaks as $break)
                <div class="rounded-lg bg-blue-50 p-4 text-sm text-gray-700">{{ $break['start'] }} ～ {{ $break['end'] }}（{{ $break['duration'] }}）</div>
              @empty
                <p class="text-sm text-gray-500">休憩履歴はありません。</p>
              @endforelse
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
        <p class="text-sm text-gray-500">申請理由</p>
        <p class="mt-2 whitespace-pre-wrap text-gray-900">{{ $correctionRequest->reason }}</p>
      </section>

      @if ($correctionRequest->isPending())
        <form method="POST" action="{{ route('admin.correction-requests.approve', $correctionRequest) }}">
          @csrf
          <button type="submit" class="touch-target w-full rounded-lg bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700 sm:w-auto">承認する</button>
        </form>
      @endif
    </div>
  </div>
</x-app-layout>
