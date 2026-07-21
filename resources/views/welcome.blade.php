<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>勤怠管理アプリ | {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-white font-sans text-gray-900 antialiased">
        <main class="app-screen mx-auto flex w-full max-w-lg flex-col items-center justify-center px-4 pt-7 sm:px-6 sm:pt-10">
            <header class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-md" aria-hidden="true">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="mt-3 text-xl font-bold tracking-wide text-gray-900">勤怠管理アプリ</p>
                <p class="mt-0.5 text-xs tracking-wider text-gray-500">Attendance Management</p>
            </header>

            <section class="mt-6 w-full text-center sm:mt-8">
                <div class="mx-auto flex h-36 w-36 items-center justify-center rounded-full bg-blue-50 sm:h-40 sm:w-40" aria-hidden="true">
                    <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-blue-600 shadow-sm ring-1 ring-blue-100 sm:h-28 sm:w-28">
                        <svg class="h-14 w-14 sm:h-16 sm:w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9h17M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            <circle cx="12" cy="14" r="4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v2.5l1.5 1" />
                        </svg>
                        <span class="absolute -end-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </div>
                </div>

                <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight text-gray-900 sm:mt-6 sm:text-4xl">
                    毎日の勤務時間を、<br>かんたん・正確に。
                </h1>
                <p class="mt-4 text-base leading-7 text-gray-600">
                    出勤・退勤・休憩・修正申請まで<br class="sm:hidden">スマホひとつで管理できます。
                </p>
            </section>

            <div class="mt-8 w-full space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-base font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        ダッシュボードへ
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-base font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        ログインする
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="flex min-h-12 w-full items-center justify-center rounded-xl border border-blue-600 bg-white px-5 py-3 text-base font-bold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            新規登録する
                        </a>
                    @endif
                @endauth
            </div>

            <aside class="mt-7 flex w-full items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 sm:gap-4 sm:px-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-blue-600 shadow-sm" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.5-3.5A11.9 11.9 0 0112 3a11.9 11.9 0 01-8.5 3.5C3.2 13.8 6.7 19 12 21c5.3-2 8.8-7.2 8.5-14.5z" />
                    </svg>
                </span>
                <span>
                    <span class="block font-bold text-gray-900">安心・安全</span>
                    <span class="mt-1 block text-sm leading-6 text-gray-600">
                        勤怠データは安全に管理されます。<br>
                        修正申請・管理者承認にも対応しています。
                    </span>
                </span>
            </aside>

            <footer class="mt-6 text-center text-xs text-gray-400">
                &copy; 2026 Attendance Management
            </footer>
        </main>
    </body>
</html>
