<x-guest-layout>
    <div class="mb-5 sm:mb-6">
        <h1 class="text-2xl font-bold text-gray-900">ログイン</h1>
        <p class="mt-1 text-sm text-gray-600">アカウントにログインしてください</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" class="mt-2 block min-h-11 w-full text-base" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="example@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="パスワード" />
            <x-text-input id="password" class="mt-2 block min-h-11 w-full text-base" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800">
            ログイン
        </button>

        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <label for="remember_me" class="inline-flex min-h-11 cursor-pointer items-center">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">ログイン状態を保持する</span>
            </label>

            @if (Route::has('password.request'))
                <a class="inline-flex min-h-11 items-center rounded-md text-sm font-medium text-blue-700 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                    パスワードを忘れましたか？
                </a>
            @endif
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 px-4 py-5 text-center sm:mt-7">
            <p class="text-sm text-gray-600">アカウントをお持ちでない方</p>
            <a href="{{ route('register') }}" class="mt-2 inline-flex min-h-11 items-center justify-center rounded-lg border border-blue-200 bg-white px-5 font-semibold text-blue-700 shadow-sm hover:border-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                新規登録はこちら
            </a>
        </div>
    @endif
</x-guest-layout>
