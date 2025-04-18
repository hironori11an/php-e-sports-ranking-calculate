# Cypressテスト実行ガイド

## 前提条件

- Node.js と npm がインストールされていること
- プロジェクトの依存関係がインストールされていること: `npm install`
- PHPサーバーが起動していること（テスト対象のアプリケーション）

## テスト実行方法

### GUI モード (開発時)

```bash
npm run cypress:open
# または
npm run e2e
```

これにより、Cypressのテストランナーが起動します。テストランナーからテストケースを選択して実行できます。

### ヘッドレスモード (CI/CD)

```bash
npm test
# または
npm run cypress:run
# または
npm run e2e:headless
```

すべてのテストをヘッドレスモードで実行します（ブラウザを表示せず）。

### 特定のブラウザでの実行

```bash
npm run cypress:run:chrome   # Chrome ブラウザで実行
npm run cypress:run:firefox  # Firefox ブラウザで実行
```

### 特定のテストファイルのみ実行

```bash
npx cypress run --spec "cypress/e2e/home.cy.js"
# または
npm run cypress:run:spec "cypress/e2e/home.cy.js"
```

## テストファイル構成

- `cypress/e2e/*.cy.js`: E2Eテストファイル
  - `home.cy.js`: ホームページの表示テスト
  - `form.cy.js`: フォーム送信のテスト
  - `file-upload.cy.js`: CSVファイルのアップロードテスト
  - `form-validation.cy.js`: フォーム入力検証テスト
  - `result.cy.js`: ランキング結果表示のテスト

- `cypress/fixtures/*.csv`: テスト用CSVファイル
  - `entry.csv`: 通常のエントリーファイル
  - `score.csv`: 通常のスコアファイル
  - `invalid_entry.csv`: 不正な形式のエントリーファイル
  - `large_entry.csv`: 大きなエントリーファイル
  - `large_score.csv`: 大きなスコアファイル

## カスタムコマンド

Cypressでは、以下のカスタムコマンドを使用できます：

- `cy.uploadFile(selector, fileName, fileType)`: ファイルアップロード用のカスタムコマンド
  ```javascript
  // 使用例
  cy.uploadFile('#entry-file', 'entry.csv', 'text/csv');
  ```

## テスト環境の設定

テスト環境の設定は `cypress.config.js` で行います：

```javascript
// cypress.config.js の例
{
  e2e: {
    baseUrl: 'http://localhost:8080', // アプリケーションのベースURL
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
  },
}
```

## サーバーの起動方法

テストを実行する前に、アプリケーションサーバーを起動する必要があります。以下のコマンドでPHPの開発サーバーを起動できます：

```bash
# PHP組み込みサーバー使用の場合
cd public
php -S localhost:8080

# docker-compose使用の場合
docker-compose up -d
```

## レポート

テスト結果のレポートはデフォルトで `cypress/videos` と `cypress/screenshots` ディレクトリに保存されます。 