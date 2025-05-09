# ゲームのランキング集計CLIプログラム、Webアプリケーション

指定されたファイルを処理して得られたランキング入賞者を標準出力に表示するCLIアプリケーション、Webアプリケーション

## 実行環境

- Docker
- PHP 8.2


## 入力ファイル仕様

### エントリーファイル

- 2列のCSVファイル（引用符、改行なし）
- 1行目はヘッダー: `player_id,handle_name`
- 2行目以降がエントリーデータ

### スコアファイル

- 3列のCSVファイル（引用符、改行なし）
- 1行目はヘッダー: `create_timestamp,player_id,score`
- 2行目以降がスコアデータ

## 出力形式

- 4列のCSVファイル形式で標準出力に出力
- 1行目はヘッダー: `rank,player_id,handle_name,score`
- 2行目以降がランキングデータ（上位10位まで）
- 同点の場合は同じランクを付与し、player_idでソート

## 開発環境の立ち上げ方

環境変数の`APP_ENV=development`の場合にXdebugをインストールし、有効になります。
Xdebugはdebugとcoverageように使用しています。
```bash
docker-compose up -d
```
## 使用方法

### CLI
```bash
php src/index.php [エントリーファイルパス] [スコアファイルパス]
```
例

```bash
php src/index.php work/entry.csv work/score.csv
```

### Web
http://localhost:8080/


## テスト実行方法

```bash
# phpunitの実行
composer test
# HTMLベースのコードカバレッジレポート
composer test-html
```

## GitHub Actions CI設定

このプロジェクトでは、GitHub Actionsを使用して継続的インテグレーション（CI）を実装しています。

### 設定内容

- mainブランチへのプルリクエスト時に自動的にPHPUnitテストが実行されます
- テストが失敗した場合、プルリクエストのマージがブロックされます
