# YokaKit

YokaKitは、Laravelを使用したWebアプリケーションです。このREADMEでは、プロジェクトのセットアップと実行方法について説明します。

## 必要条件

- Docker 20.10.13以上
- Docker Compose v2.17.2以上

## Dockerのインストール

Dockerがインストールされていない場合は、以下の手順でインストールしてください：

1. Dockerをインストールします：
   ```bash
   curl -fsSL https://get.docker.com | sudo sh
   ```
   ※ 環境に応じて公式ドキュメントの最新手順を確認してください

2. 現在のユーザーをdockerグループに追加します：
   ```bash
   sudo usermod -aG docker $USER
   newgrp docker  # グループ変更を即時反映
   ```

3. インストールを確認します：
   ```bash
   docker --version
   docker compose version  # Compose v2を確認
   ```

## セットアップ手順

1. リポジトリをクローンします：
   ```bash
   git clone <your-yokakit-repository-url>
   cd YokaKit
   ```

2. セットアップスクリプトを実行：
   ```bash
   chmod +x setup.sh
   ./setup.sh  # .envファイル生成と設定確認
   ```
   ※ DBパスワードは12文字以上の英数字記号を推奨

3. Dockerコンテナをビルド＆起動：
   - 初回またはDockerfile変更時
   ```bash
   docker compose up -d --build
   ```
   - 通常起動時（2回目以降）
   ```bash
   docker compose up -d
   ```

   ※ 新しいマルチステージDockerfile採用により、開発環境では自動的にdevelopmentステージが使用されます

4. 依存関係インストールとデータベース移行：
   ```bash
   docker compose exec yokakit-web-app composer install
   docker compose exec yokakit-web-app php artisan migrate
   ```

5. （オプション）テストデータ投入：
   ```bash
   docker compose exec yokakit-web-app php artisan db:seed
   ```

6. （オプション）管理者ユーザー作成：
   ```bash
   docker compose exec yokakit-web-app php artisan make:user admin \
     admin@example.com 'StrongP@ssw0rd!'
   ```
   ※ パスワードはシングルクォートで囲んでください

## 使用方法

- ローカルアクセス：http://localhost:18080
- リモートアクセス：http://<サーバーIP>:18080

※ ファイアウォール設定でポート18080と8081（Laravel Reverb WebSocket）を開放してください

## 開発

### VS Code Devcontainer / GitHub Codespaces

本プロジェクトはVS Code DevcontainerとGitHub Codespaces対応しています。以下の機能が利用可能です：

1. VS Code Devcontainerでの開発:
   - VS Codeで「Dev Containersで再度開く」を選択
   - 必要な拡張機能が自動的にインストール
   - Laravel開発用の設定が自動的に適用

2. GitHub Codespaces対応:
   - GitHubウェブサイトから「Code」→「Codespaces」→「新しいcodespace」
   - ブラウザ上でVS Code環境が利用可能
   - 自動的に開発環境が構築

3. Docker開発サポート:
   - コンテナ内でのDocker操作が可能
   - VS Code Docker拡張機能によるコンテナ管理
   - Docker Buildxとcompose v2対応

### 環境変数
`.env`ファイルの主な設定項目：
```ini
# Laravel Reverb WebSocket設定（外部サービス不要）
BROADCAST_DRIVER=reverb
REVERB_APP_ID=yokakit
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8081
```

### 常用コマンド

#### アプリケーション監視
```bash
docker compose logs -f yokakit-web-app  # アプリケーションログ監視
docker compose stats                    # リアルタイムリソース監視
```

#### マルチステージDockerビルド
```bash
docker build --target production .   # 本番環境用ビルド
docker build --target development .  # 開発環境用ビルド
```

#### 開発・テスト
```bash
docker compose exec yokakit-web-app php artisan test --coverage  # テスト実行（カバレッジ付き）
docker compose exec yokakit-web-app ./vendor/bin/pint            # コード整形
docker compose exec yokakit-web-app ./vendor/bin/phpstan analyse # 静的解析
```

#### MQTT監視
```bash
docker compose exec mqtt mosquitto_sub -h mqtt -p 1883 -t 'production/#'
```

## トラブルシューティング

キャッシュ問題が疑われる場合：
```bash
docker compose exec yokakit-web-app php artisan optimize:clear
docker compose exec yokakit-web-app php artisan route:cache
docker compose exec yokakit-web-app php artisan config:cache
```

コンテナ再構築（根本解決が必要な場合）：
```bash
docker compose down -v --remove-orphans
docker compose up -d --build
```

## CI/CD & マルチアーキテクチャ対応

このプロジェクトでは以下の自動化とマルチアーキテクチャビルドを実施しています：

- **GitHub Actions**: プルリクエストとmainブランチへのプッシュ時に自動ビルド・テスト実行
- **マルチアーキテクチャ**: AMD64とARM64の両方に対応したDockerイメージ
- **GitHub Container Registry**: ghcr.ioへの自動イメージ公開
- **キャッシュ管理**: 7日間の保持期間での自動クリーンアップ

### ワークフロー

1. **docker-build.yml**: マルチアーキテクチャビルドとテスト
2. **docker-publish.yml**: コンテナレジストリへの公開
3. **cache-cleanup.yml**: ビルドキャッシュの自動管理

## テスト

プロジェクトは包括的なテストカバレッジを提供しています：

```bash
# 全テスト実行
docker compose exec yokakit-web-app php artisan test

# 並列実行（高速化）
docker compose exec yokakit-web-app php artisan test --parallel --processes=4

# カバレッジレポート付き
docker compose exec yokakit-web-app php artisan test --coverage
```

現在のテスト状況：
- **425/425 tests passing (100%)** ✅
- **Unit Tests**: Model層の完全なカバレッジ
- **Feature Tests**: HTTP/Controller層の完全なカバレッジ
- **Browser Tests**: Laravel Duskによるブラウザテスト

## ライセンス

[Apache License 2.0](LICENSE)
