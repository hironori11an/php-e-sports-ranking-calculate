# ゲームのランキング集計CLIプログラム

指定されたファイルを処理して得られたランキング入賞者を標準出力に表示するCLIアプリケーションです。

## 実行環境

- PHP 8.2以上
- Composer

## インストール方法

```bash
# 依存パッケージのインストール
composer install
```

## 使用方法

```bash
php src/index.php [エントリーファイルパス] [スコアファイルパス]
```

### 例

```bash
php src/index.php game_entry_log.csv game_score_log.csv
```

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

## テスト実行方法

```bash
# 
# phpunitの実行
composer test
# HTMLベースのコードカバレッジレポート
composer test-html
``` 