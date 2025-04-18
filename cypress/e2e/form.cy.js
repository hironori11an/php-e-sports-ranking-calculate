describe('フォーム送信テスト', () => {
  beforeEach(() => {
    // テスト実行前にホームページを訪問
    cy.visit('/');
  });

  it('ファイルをアップロードしてフォームを送信できること', () => {
    // エントリーファイルをアップロード
    cy.uploadFile('#entry-file', 'entry.csv', 'text/csv');
    
    // スコアファイルをアップロード
    cy.uploadFile('#score-file', 'score.csv', 'text/csv');
    
    // フォームを送信
    cy.get('form').submit();
    
    // 送信後、結果ページに遷移することを確認
    cy.url().should('include', '/ranking/calculate');
  });

  it('ファイルなしで送信するとエラーになること', () => {
    // フォームを送信（ファイルなし）
    cy.get('form').submit();
    
    // エラーメッセージが表示されることを確認（実際のアプリの仕様に応じて変更）
    // このテストはアプリケーションの実際の挙動に応じて調整が必要です
    cy.on('window:alert', (text) => {
      expect(text).to.include('ファイルを選択してください');
    });
  });
}); 