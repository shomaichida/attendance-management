<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * 指定したユーザーのパスワードを対話形式で安全に再設定するコマンド。
 */
class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email : パスワードを再設定するユーザーのメールアドレス}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定したユーザーのパスワードを対話形式で再設定します';

    /**
     * 指定ユーザーの保存状態を確認し、新しいパスワードへ更新する。
     */
    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error("指定されたメールアドレスのユーザーが見つかりません: {$email}");

            return self::FAILURE;
        }

        $storedPassword = $user->getRawOriginal('password');

        $this->components->info('対象ユーザーを確認しました。');
        $this->line("メールアドレス: {$user->email}");
        $this->line('passwordの状態: '.($storedPassword === null ? 'NULL' : '値あり'));
        $this->line('passwordの文字数: '.($storedPassword === null ? '0' : strlen((string) $storedPassword)));
        $this->line('ハッシュ識別子: '.$this->hashIdentifier($storedPassword));

        $password = $this->secret('新しいパスワードを入力してください');
        $passwordConfirmation = $this->secret('確認のため、もう一度入力してください');

        $validator = Validator::make(
            [
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
            ],
            [
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
            ],
            [
                'password.required' => '新しいパスワードを入力してください。',
                'password.confirmed' => '確認用パスワードと一致しません。',
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $hashedPassword = Hash::make((string) $password);

        // Userモデルのhashedキャストを通さず、Hash::makeを一度だけ適用した値を保存する。
        DB::table($user->getTable())
            ->where($user->getKeyName(), $user->getKey())
            ->update([
                'password' => $hashedPassword,
                'updated_at' => now(),
            ]);

        $this->components->info("{$user->email} のパスワードを再設定しました。");

        return self::SUCCESS;
    }

    /**
     * 保存済みパスワードを公開せず、既知のハッシュ識別子だけを返す。
     */
    private function hashIdentifier(mixed $storedPassword): string
    {
        if ($storedPassword === null) {
            return 'なし';
        }

        $password = (string) $storedPassword;

        return match (true) {
            str_starts_with($password, '$2y$') => '$2y$ (bcrypt)',
            str_starts_with($password, '$2b$') => '$2b$ (bcrypt)',
            str_starts_with($password, '$argon2id$') => '$argon2id$',
            str_starts_with($password, '$argon2i$') => '$argon2i$',
            default => '不明（Laravel標準ハッシュではない可能性）',
        };
    }
}
