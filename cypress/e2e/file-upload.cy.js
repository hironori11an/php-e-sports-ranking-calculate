describe('CSVファイルアップロードテスト', () => {
  beforeEach(() => {
    cy.visit('/');
  });

  it('エントリーファイルのみアップロードした場合のバリデーション', () => {
    // エントリーファイルのみをアップロード
    cy.uploadFile('#entry-file', 'entry.csv', 'text/csv');
    
    // フォームを送信
    cy.get('form').submit();
    
    // エラーメッセージが表示されることを確認（アプリの実装に応じて変更）
    // このテストはアプリケーションの実際の挙動に応じて調整が必要です
    cy.on('window:alert', (text) => {
      expect(text).to.include('スコアファイルを選択してください');
    });
  });

  it('スコアファイルのみアップロードした場合のバリデーション', () => {
    // スコアファイルのみをアップロード
    cy.uploadFile('#score-file', 'score.csv', 'text/csv');
    
    // フォームを送信
    cy.get('form').submit();
    
    // エラーメッセージが表示されることを確認（アプリの実装に応じて変更）
    // このテストはアプリケーションの実際の挙動に応じて調整が必要です
    cy.on('window:alert', (text) => {
      expect(text).to.include('エントリーファイルを選択してください');
    });
  });

  it('両方のファイルをアップロードして送信できること', () => {
    // エントリーファイルをアップロード
    cy.uploadFile('#entry-file', 'entry.csv', 'text/csv');
    
    // スコアファイルをアップロード
    cy.uploadFile('#score-file', 'score.csv', 'text/csv');
    
    // アップロード後にファイル名が表示されることを確認（ブラウザの挙動に依存）
    // 注: 実際のブラウザ挙動に依存するためスキップすることもあります
    
    // フォームを送信
    cy.get('form').submit();
    
    // 送信後、結果ページに遷移することを確認
    cy.url().should('include', '/ranking/calculate');
  });
}); 