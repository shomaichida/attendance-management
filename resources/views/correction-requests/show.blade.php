<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-gray-900">修正申請詳細</h2>
            <a href="{{ route('correction-requests.index', ['status' => $correctionRequest->status]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">申請履歴へ戻る</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
            @endif

            <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                <div class="grid gap-6 sm:grid-cols-3">
                    <div><p class="text-sm text-gray-500">対象日</p><p class="mt-1 font-semibold">{{ $correctionRequest->attendance->work_date->format('Y/m/d') }}</p></div>
                    <div><p class="text-sm text-gray-500">申請日時</p><p class="mt-1 font-semibold">{{ $correctionRequest->created_at->format('Y/m/d H:i') }}</p></div>
                    <div><p class="text-sm text-gray-500">状態</p><p class="mt-1 font-semibold {{ $correctionRequest->statusColor() }}">{{ $correctionRequest->statusLabel() }}</p></div>
                </div>
            </section>

            <section class="grid gap-6 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                    <h3 class="font-bold text-gray-700">修正前</h3>
                    <p class="mt-4 text-sm">出勤：{{ $correctionRequest->original_clock_in?->format('H:i') ?? '-' }}</p>
                    <p class="mt-2 text-sm">退勤：{{ $correctionRequest->original_clock_out?->format('H:i') ?? '-' }}</p>
                    <div class="mt-4 space-y-2">
                        @forelse ($originalBreaks as $break)
                            <p class="rounded-lg bg-gray-50 p-3 text-sm">休憩：{{ $break['start'] }} ～ {{ $break['end'] }}</p>
                        @empty
                            <p class="text-sm text-gray-500">休憩はありません。</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-6">
                    <h3 class="font-bold text-blue-700">修正後</h3>
                    <p class="mt-4 text-sm">出勤：{{ $correctionRequest->requested_clock_in?->format('H:i') ?? '-' }}</p>
                    <p class="mt-2 text-sm">退勤：{{ $correctionRequest->requested_clock_out?->format('H:i') ?? '-' }}</p>
                    <div class="mt-4 space-y-2">
                        @forelse ($requestedBreaks as $break)
                            <p class="rounded-lg bg-blue-50 p-3 text-sm">休憩：{{ $break['start'] }} ～ {{ $break['end'] }}</p>
                        @empty
                            <p class="text-sm text-gray-500">休憩はありません。</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                <p class="text-sm text-gray-500">申請理由</p>
                <p class="mt-2 whitespace-pre-wrap text-gray-900">{{ $correctionRequest->reason }}</p>
            </section>
        </div>
    </div>
</x-app-layout>
