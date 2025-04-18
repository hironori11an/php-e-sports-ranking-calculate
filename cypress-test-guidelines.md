# Cypressテスト開発ガイドライン

## テスト作成の基本原則

1. **シンプルさを保つ**
   - テストはシンプルで理解しやすいものにする
   - 複雑なロジックはカスタムコマンドに分離する
   - 一つのテストは一つの機能やシナリオに集中する

2. **安定性の確保**
   - フラッキーテスト（不安定なテスト）を避ける
   - 固定セレクタやIDを優先的に使用
   - 非決定的な要素（タイミング依存など）に注意

3. **メンテナンス性の向上**
   - テストコードは整理整頓し、コメントを適切に入れる
   - 重複コードは共通化する
   - テスト失敗時に原因を特定しやすくする

## テスト設計パターン

### ページオブジェクトモデル（必要に応じて）

大規模なアプリケーションでは、ページオブジェクトモデルの採用を検討する：

```javascript
// cypress/support/pages/HomePage.js
class HomePage {
  visit() {
    cy.visit('/');
  }
  
  getEntryFileInput() {
    return cy.get('#entry-file');
  }
  
  getScoreFileInput() {
    return cy.get('#score-file');
  }
  
  submitForm() {
    cy.get('form').submit();
  }
}

export default new HomePage();
```

### カスタムコマンドの活用

共通処理はカスタムコマンドとして実装：

```javascript
// 既に実装済みのuploadFileコマンドの例
Cypress.Commands.add('uploadFile', (selector, fileName, fileType = '') => {
  cy.get(selector).then(subject => {
    cy.fixture(fileName, 'binary')
      .then(Cypress.Blob.binaryStringToBlob)
      .then(blob => {
        const el = subject[0];
        const testFile = new File([blob], fileName, { type: fileType });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(testFile);
        el.files = dataTransfer.files;
        return cy.wrap(subject).trigger('change', { force: true });
      });
  });
});
```

## テスト実装のベストプラクティス

1. **セレクタの選定**
   - ID > データ属性 > クラス > タグ の順で優先度を付ける
   - データテスト属性を使用することを推奨: `data-testid="submit-button"`
   - XPathは最終手段として使用する

2. **アサーションの書き方**
   - 明確で具体的なアサーションを書く
   - 複数の関連するアサーションをグループ化する
   - 過度なアサーションは避ける

3. **待機と同期**
   - 明示的な待機を使用: `cy.wait()`は最小限に
   - アサーションベースの待機を優先: `.should('be.visible')`
   - 長い操作には適切なタイムアウトを設定: `{ timeout: 10000 }`

4. **テストの独立性**
   - 各テストは他のテストに依存しないようにする
   - テスト前に必要な前提条件を設定
   - `beforeEach()`でテスト環境をリセット

## テストデータの管理

1. **フィクスチャの利用**
   - テストデータはフィクスチャとして管理
   - 環境や条件ごとにデータを分ける
   - バージョン管理システムに含める

2. **環境変数の活用**
   - 環境依存の設定は環境変数で管理
   - `cypress.env.json`ファイルを利用

## テスト実行とデバッグ

1. **効率的なデバッグ**
   - `.debug()`を使ってテストの途中で停止
   - スクリーンショットと動画を活用
   - コンソールログを適切に使用

2. **CIでの実行**
   - ヘッドレスモードで実行: `cypress run`
   - 失敗したテストのスクリーンショットを保存
   - タイムアウト設定を適切に調整

## 拡張とプラグイン

必要に応じて以下のプラグインの導入を検討：

1. **cypress-file-upload**: ファイルアップロードテストの拡張
2. **cypress-axe**: アクセシビリティテスト
3. **cypress-real-events**: 実際のユーザーイベントをシミュレート

## コード品質の維持

1. **コードレビュー**
   - テストコードもアプリケーションコードと同様にレビュー
   - ベストプラクティスに沿っているか確認

2. **リファクタリング**
   - 定期的にテストコードをリファクタリング
   - 共通パターンを抽出してカスタムコマンド化 