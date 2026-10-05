###勤怠管理アプリ###

## 作成者

戸田めぐみ

## 使用技術

- PHP 8.2
- Laravel 10.x
- MySQL 8.0
- Nginx
- Docker / Docker Compose / Laravel Sail
- Vite / Tailwind CSS 3.4
- Laravel Fortify（認証）
- Laravel Policy (認可)
- phpMyAdmin
- GitHub

## ER図

erDiagram
users ||--o{ attendance_records : "1つのユーザーは複数の勤怠実績を持つ"
users ||--o{ applications : "1つのユーザーは複数の申請を出す"
users ||--o{ monthly_attendances : "1つのユーザーは複数の月次集計を持つ"
users ||--o{ summary_reports : "1つのユーザーは複数のサマリーを持つ"

attendance_records ||--o{ breaks : "1日の勤怠は複数の休憩実績を持つ"
attendance_records |o--o{ applications : "1日の勤怠に対して複数の修正申請が出される"

applications ||--o{ proposal_breaks : "1つの申請は複数の変更用休憩を持つ"

users {
bigint id PK
string name
string email
string password
tinyint_unsigned role "0:user、1:admin"
time scheduled_work_start
time scheduled_work_end
timestamp created_at
timestamp updated_at
}

attendance_records {
bigint id PK
bigint_unsigned user_id FK
date date "勤務日"
timestamp clock_in "出勤時間"
timestamp clock_out "退勤時間"
int_unsigned total_break_time "休憩(分)"
int_unsigned total_time "合計"
text comment "管理者:備考"
timestamp created_at
timestamp updated_at
}

attendance_breaks {
bigint id PK
bigint_unsigned attendance_record_id FK
timestamp break_in "休憩入り"
timestamp break_out "休憩戻り"
timestamp created_at
timestamp updated_at
}

applications {
bigint id PK
bigint_unsigned user_id FK
bigint_unsigned attendance_record_id FK "NULL許容"
tinyint_unsigned approval_status "0:承認待ち、1:承認済み"
date new_date "対象日"
timestamp new_clock_in
timestamp new_clock_out
text comment "申請理由"
timestamp application_date "申請日時"
timestamp created_at
timestamp updated_at
}

proposal_breaks {
bigint id PK
bigint_unsigned application_id FK
bigint_unsigned attendance_break_id FK
timestamp new_break_in
timestamp new_break_out
timestamp created_at
timestamp updated_at
}

monthly_attendances {
bigint id PK
bigint_unsigned user_id FK
date month "対象の月"
int_unsigned work_minutes "一月の労働時間(分)"
int_unsigned overtime "一月の残業時間(分)"
int_unsigned late_count "遅刻回数"
int_unsigned early_leave_count "早退回数"
int_unsigned long_work_count "長時間労働日数"
timestamp created_at
timestamp updated_at
}

summary_reports {
bigint id PK
bigint_unsigned user_id FK
date start_date
date end_date
int_unsigned total_work_minutes "総労働時間(分)"
int_unsigned total_overtime_minutes "総残業時間(分)"
int_unsigned avg_work_minutes "平均労働時間(分)"
timestamp created_at
timestamp updated_at
}

## 環境構築手順

1. **リポジトリをクローン**
   ターミナルを開き、プロジェクトを配置したいディレクトリで以下を実行します。

    ```bash
    git clone <リポジトリのURL>
    cd attendance-app
    ```

2. **.envファイル（環境変数）の作成と準備**

    `.env.example`(設定ファイルの雛形) をコピーして `.env` を作成します。

    ```bash
    cp .env.example .env
    ```

".env ファイルを開き、データベース接続情報が以下と一致していることを確認して下さい。もしくはSail向けに編集して下さい。

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=sail
    DB_PASSWORD=password

    MAIL_MAILER=smtp
    MAIL_HOST=mailpit
    MAIL_PORT=1025
    ```

3.  **Composer依存パッケージのインストール**
    プロジェクトの初回セットアップ時は、`vendor` ディレクトリが存在しないため `sail` コマンドを使用できません。
    以下のDockerコマンドを実行して、一時的なコンテナを使用して`vendor` ディレクトリを生成。

    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php82-composer:latest \
        composer install --ignore-platform-reqs
    ```

4.  **Laravel Sailの起動**

    以下のコマンドでDockerコンテナを起動します。

    ```bash
    ./vendor/bin/sail up -d
    ```

    > **エイリアスの設定（推奨）**
    >
    > 毎回 `./vendor/bin/sail` と入力するのは手間なので、エイリアスを設定すると便利です。
    >
    > ```bash
    > alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
    > ```

5.  **アプリケーションキーの生成**

    ```bash
    sail artisan key:generate
    ```

6.  **データベースのマイグレーションと初期データ投入**

    以下のコマンドでテーブルを作成し、ダミーデータを投入します。

    ```bash
    sail artisan migrate:fresh --seed
    ```

7.  **フロントエンドのビルド**

    ```bash
    sail npm install
    sail npm install alpinejs
    sail npm run dev
    ```

### sail npm install alpinejsはpackage.json というファイルを開き、その中の "dependencies" または "devDependencies"alpinejsが記載されている場合はこのコマンドは必要ないので記述不要

    `npm run dev` は開発中は起動したままにしてください。

8.  **アプリケーションへのアクセス**

        アプリ: http://localhost
        phpMyAdmin: http://localhost:8080
        Mailpit: http://localhost:8025

## テスト実行

```bash
sail artisan test
```

カバレッジ付きで実行する場合:

```bash
sail artisan test --coverage
```

## コンテナの停止方法

開発を終了する場合は、以下のコマンドでコンテナを安全に停止させてください。

```bash
./vendor/bin/sail down
```

## 機能一覧

- ユーザー認証（登録、ログイン、ログアウト）
- 勤怠打刻機能
- 一般ユーザー:表示機能（月次勤怠の一覧、詳細）、勤怠の修正申請機能
- 管理者:表示機能（全スタッフの一覧、スタッフ詳細、全ユーザーの日次勤怠一覧、勤怠詳細、申請一覧）、承認申請機能
