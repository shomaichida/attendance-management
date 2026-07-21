# 勤怠管理アプリ

**Attendance Management System**

Laravelで開発した勤怠管理システムです。

一般ユーザーによる勤怠登録から、  
管理者による修正申請承認・CSV出力までを実装しています。

スマートフォン表示にも対応しています。

---

## アプリイメージ

![Application Preview](docs/images/top-preview.png)

Welcome画面・ログイン画面・勤怠一覧・管理者画面など、  
主要画面をまとめたプレビュー画像です。

---

## 主な機能

| 画面・機能 | 一般ユーザー | 管理者 |
| --- | :---: | :---: |
| welcome画面 | ○ | ○ |
| ログイン・新規登録 | ○ | ○ |
| 出勤・退勤 | ○ | ○ |
| 休憩開始・休憩終了 | ○ | ○ |
| 勤怠一覧 | 自分の勤怠を確認 | 全スタッフの勤怠を確認 |
| 勤怠詳細 | 自分の勤怠を確認 | スタッフごとの勤怠を確認 |
| 勤怠編集 | 修正申請として送信 | 直接編集可能 |
| 修正申請一覧・詳細 | 自分の申請を確認 | 全スタッフの申請を確認 |
| 修正申請承認 | − | ○ |
| スタッフ一覧 | − | ○ |
| 管理者ダッシュボード | − | ○ |
| CSV出力 | − | ○ |

## 使用技術

| 分類 | 技術 |
| --- | --- |
| バックエンド | Laravel / PHP |
| データベース | MySQL |
| テンプレート | Blade |
| スタイリング | Tailwind CSS |
| フロントエンドビルド | Vite / Node.js / npm |
| パッケージ管理 | Composer |

## 画面一覧

- welcome画面
- ログイン画面
- 新規登録画面
- 勤怠一覧画面
- 勤怠詳細画面
- 勤怠修正申請画面
- 修正申請一覧・詳細画面
- 管理者ダッシュボード
- スタッフ一覧画面
- 管理者用勤怠一覧・詳細・編集画面
- 管理者用修正申請一覧・承認画面

# 画面イメージ

## Welcome画面

![Welcome画面](docs/images/welcome.png)

勤怠管理アプリのトップページです。  
ログイン・新規登録への導線とアプリ概要を表示しています。

---

## ログイン画面

![ログイン画面](docs/images/login.png)

スマートフォンにも対応したログイン画面です。  
ログイン・新規登録をタブ形式で切り替えられます。

---

## 新規登録画面

![新規登録画面](docs/images/register.png)

アカウント作成画面です。  
スマートフォンでも入力しやすいレイアウトになっています。

---

## 一般ユーザーダッシュボード

![ダッシュボード](docs/images/dashboard.png)

出勤・退勤・休憩開始・休憩終了を行う画面です。

---

## 勤怠一覧

![勤怠一覧](docs/images/attendance-list.png)

月別の勤怠一覧を表示します。

---

## 勤怠詳細・修正申請

![勤怠詳細](docs/images/attendance-detail.png)

勤務時間・休憩時間の確認と修正申請を行えます。

---

## 修正申請一覧

![修正申請一覧](docs/images/correction-list.png)

申請した修正内容と承認状態を確認できます。

---

## 管理者ダッシュボード

![管理者ダッシュボード](docs/images/admin-dashboard.png)

管理者向けメニューです。

---

## スタッフ一覧

![スタッフ一覧](docs/images/employees.png)

全スタッフの勤怠情報へアクセスできます。

---

## 修正申請承認

![修正申請承認](docs/images/approval.png)

一般ユーザーから送信された修正申請を確認・承認できます。

---

## CSV出力

![CSV出力](docs/images/csv.png)

対象月の勤怠情報をCSV形式でダウンロードできます。

---

## ER図

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar employee_number UK
        varchar name
        varchar email UK
        timestamp email_verified_at "nullable"
        varchar password
        varchar role "default: employee"
        varchar department "nullable"
        boolean is_active "default: true"
        varchar remember_token "nullable"
        timestamp created_at "nullable"
        timestamp updated_at "nullable"
    }

    ATTENDANCES {
        bigint id PK
        bigint user_id FK
        date work_date
        datetime clock_in "nullable"
        datetime clock_out "nullable"
        text memo "nullable"
        timestamp created_at "nullable"
        timestamp updated_at "nullable"
    }

    ATTENDANCE_BREAKS {
        bigint id PK
        bigint attendance_id FK
        datetime break_start
        datetime break_end "nullable"
        int break_minutes "default: 0"
        timestamp created_at "nullable"
        timestamp updated_at "nullable"
    }

    ATTENDANCE_CORRECTION_REQUESTS {
        bigint id PK
        bigint attendance_id FK
        bigint user_id FK
        varchar status "default: pending, indexed"
        text reason
        datetime original_clock_in "nullable"
        datetime requested_clock_in "nullable"
        datetime original_clock_out "nullable"
        datetime requested_clock_out "nullable"
        json original_breaks "nullable"
        json requested_breaks "nullable"
        bigint approved_by FK "nullable"
        datetime approved_at "nullable"
        timestamp created_at "nullable"
        timestamp updated_at "nullable"
    }

    USERS ||--o{ ATTENDANCES : "has"
    ATTENDANCES ||--o{ ATTENDANCE_BREAKS : "has"
    USERS ||--o{ ATTENDANCE_CORRECTION_REQUESTS : "requests"
    ATTENDANCES ||--o{ ATTENDANCE_CORRECTION_REQUESTS : "has"
    USERS o|--o{ ATTENDANCE_CORRECTION_REQUESTS : "approves (approved_by)"
```

### テーブル説明

- `users`：一般ユーザー・管理者のアカウント情報、社員番号、所属部署、利用状態を管理します。
- `attendances`：ユーザーごとの勤務日、出勤時刻、退勤時刻を日単位で管理します。
- `attendance_breaks`：勤怠に紐づく複数回の休憩開始・終了時刻と休憩時間を管理します。
- `attendance_correction_requests`：勤怠の修正前・修正後の内容、申請理由、承認状態、承認者を管理します。

### 勤怠修正申請の流れ

1. 一般ユーザーが自分の勤怠に対する修正を申請します。
2. 申請内容を `attendance_correction_requests` に `pending` 状態で保存します。この時点では元の勤怠データを変更しません。
3. 管理者が申請内容を確認して承認します。
4. 承認された出退勤時刻と複数休憩を `attendances` および `attendance_breaks` に反映します。
5. 申請状態を `approved` に更新し、承認者と承認日時を記録します。

## セットアップ

### 1. リポジトリをクローン

```bash
git clone <repository-url>
cd attendance-management
```

### 2. PHP依存パッケージをインストール

```bash
composer install
```

### 3. フロントエンド依存パッケージをインストール

```bash
npm install
```

### 4. 環境設定ファイルを作成

```bash
cp .env.example .env
```

`.env`のデータベース接続情報を、使用するMySQL環境に合わせて設定してください。

### 5. アプリケーションキーを生成

```bash
php artisan key:generate
```

### 6. データベースを作成

```bash
php artisan migrate
```

### 7. フロントエンドをビルド

```bash
npm run build
```

### 8. 開発サーバーを起動

```bash
php artisan serve
```

起動後、ブラウザから `http://127.0.0.1:8000` にアクセスしてください。

## テスト

以下のコマンドでテストを実行できます。

```bash
php artisan test
```

## ディレクトリ構成

```text
attendance-management/
├── app/          # Controller、Model、Middleware、Actionなど
├── database/     # Migration、Factory、Seeder
├── public/       # 公開ファイルとビルド済みアセット
├── resources/    # Blade、CSS、JavaScript
├── routes/       # Webルートなどのルーティング定義
└── tests/        # Featureテスト、Unitテスト
```

## 特徴・工夫した点

- 1件の勤怠に対する複数回の休憩登録・編集に対応
- 一般ユーザーの修正申請から管理者承認までのワークフローを実装
- 対象月の勤怠データをUTF-8 BOM付きCSVとして出力
- PCとスマートフォンの両方で操作しやすいレスポンシブデザイン
- Middlewareと所有者確認による管理者・一般ユーザーの権限制御

## 今後追加予定

- 夜勤・日付をまたぐ勤務への対応
- スタッフや勤怠情報の検索
- 一覧画面のページネーション
- 修正申請の却下
- 承認コメント
- メール通知

## ライセンス

MIT License

## 作者

- GitHub: `<GitHubアカウントを追加>`
