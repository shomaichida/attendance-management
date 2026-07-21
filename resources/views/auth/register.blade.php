<x-guest-layout>
    <div class="mb-5 sm:mb-6">
        <h1 class="text-2xl font-bold text-gray-900">新規登録</h1>
        <p class="mt-1 text-sm text-gray-600">アカウントを作成してください</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="氏名" />
            <x-text-input id="name" class="mt-2 block min-h-11 w-full text-base" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="山田 太郎" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" class="mt-2 block min-h-11 w-full text-base" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="example@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="パスワード" />
            <x-text-input id="password" class="mt-2 block min-h-11 w-full text-base" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="パスワード（確認）" />
            <x-text-input id="password_confirmation" class="mt-2 block min-h-11 w-full text-base" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800">
            新規登録
        </button>
    </form>

    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 px-4 py-5 text-center sm:mt-7">
        <p class="text-sm text-gray-600">すでにアカウントをお持ちの方</p>
        <a href="{{ route('login') }}" class="mt-2 inline-flex min-h-11 items-center justify-center rounded-lg border border-blue-200 bg-white px-5 font-semibold text-blue-700 shadow-sm hover:border-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            ログインはこちら
        </a>
    </div>
</x-guest-layout>
