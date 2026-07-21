<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h2 class="text-2xl font-bold text-gray-900">📝 勤怠修正申請</h2>
      <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
        管理者ダッシュボードへ戻る
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
      <div class="flex rounded-xl border border-gray-100 bg-white p-2 shadow-sm">
        <a
          href="{{ route('admin.correction-requests.index', ['status' => 'pending']) }}"
          class="touch-target flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold {{ $status === 'pending' ? 'bg-yellow-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}"
        >
          承認待ち
        </a>
        <a
          href="{{ route('admin.correction-requests.index', ['status' => 'approved']) }}"
          class="touch-target flex-1 rounded-lg px-3 py-2 text-center text-sm font-semibold {{ $status === 'approved' ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}"
        >
          承認済み
        </a>
      </div>

      <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <p class="mobile-scroll-hint px-4 pt-4">横にスクロールして申請を確認できます →</p>
        <div class="table-scroll">
          <table class="min-w-[900px] divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold text-gray-500">状態</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold text-gray-500">申請者名</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold text-gray-500">対象日</th>
                <th class="min-w-64 px-6 py-3 text-left text-xs font-semibold text-gray-500">申請理由</th>
                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold text-gray-500">申請日時</th>
                <th class="whitespace-nowrap px-6 py-3 text-right text-xs font-semibold text-gray-500">詳細</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse ($correctionRequests as $correctionRequest)
                <tr class="hover:bg-gray-50">
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold {{ $correctionRequest->statusColor() }}">{{ $correctionRequest->statusLabel() }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">{{ $correctionRequest->user->name }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $correctionRequest->attendance->work_date->format('Y/m/d') }}</td>
                  <td class="max-w-sm truncate px-6 py-4 text-sm text-gray-600">{{ $correctionRequest->reason }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $correctionRequest->created_at->format('Y/m/d H:i') }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <a href="{{ route('admin.correction-requests.show', $correctionRequest) }}" class="touch-target rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">詳細</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                    {{ $status === 'pending' ? '承認待ちの申請はありません。' : '承認済みの申請はありません。' }}
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</x-app-layout>
