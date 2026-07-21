<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>勤怠管理アプリ | {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-slate-100 font-sans text-gray-900 antialiased">
        <div class="app-screen flex flex-col items-center justify-center px-4 pt-6 sm:pt-9">
            <a href="/" class="flex flex-col items-center rounded-xl px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm" aria-hidden="true">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="mt-1.5">
                    <span class="block text-lg font-bold tracking-wide text-gray-900">勤怠管理アプリ</span>
                    <span class="mt-0.5 block text-xs tracking-wide text-gray-500">Attendance Management</span>
                </span>
            </a>

            <main class="mt-4 w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 py-5 shadow-lg sm:mt-6 sm:px-8 sm:py-7">
                @if (Route::has('login') && Route::has('register'))
                    <nav class="grid grid-cols-2 gap-2" aria-label="認証メニュー">
                        <a
                            href="{{ route('login') }}"
                            @class([
                                'flex min-h-12 items-center justify-center rounded-xl border px-3 py-2 text-base transition focus:outline-none focus:ring-2 focus:ring-blue-500',
                                'border-blue-600 bg-blue-600 font-bold text-white shadow-sm' => request()->routeIs('login'),
                                'border-blue-200 bg-white font-semibold text-blue-700 hover:border-blue-600 hover:bg-blue-50' => ! request()->routeIs('login'),
                            ])
                            @if (request()->routeIs('login')) aria-current="page" @endif
                        >
                            ログイン
                        </a>
                        <a
                            href="{{ route('register') }}"
                            @class([
                                'flex min-h-12 items-center justify-center rounded-xl border px-3 py-2 text-base transition focus:outline-none focus:ring-2 focus:ring-blue-500',
                                'border-blue-600 bg-blue-600 font-bold text-white shadow-sm' => request()->routeIs('register'),
                                'border-blue-200 bg-white font-semibold text-blue-700 hover:border-blue-600 hover:bg-blue-50' => ! request()->routeIs('register'),
                            ])
                            @if (request()->routeIs('register')) aria-current="page" @endif
                        >
                            新規登録
                        </a>
                    </nav>
                @endif

                <div class="mt-5 sm:mt-6">
                    {{ $slot }}
                </div>
            </main>

            <footer class="mt-6 text-center text-xs text-gray-400">
                &copy; 2026 Attendance Management
            </footer>
        </div>
    </body>
</html>
