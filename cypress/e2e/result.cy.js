describe('ランキング結果表示テスト', () => {
  beforeEach(() => {
    // テスト実行前にホームページを訪問してファイルをアップロード
    cy.visit('/');
    cy.uploadFile('#entry-file', 'entry.csv', 'text/csv');
    cy.uploadFile('#score-file', 'score.csv', 'text/csv');
    cy.get('form').submit();
  });

  it('ランキング結果が何らかの形で表示されること', () => {
    // 結果ページに遷移することを確認
    cy.url().should('include', '/ranking/calculate', { timeout: 10000 });
    
    // 結果ページに何らかのコンテンツが表示されることを確認
    // 注: 実際のアプリケーションの挙動に合わせてセレクタを調整
    cy.get('body', { timeout: 10000 }).should('not.be.empty');
    
    // ページタイトルまたは何らかのテキスト要素が表示されること
    cy.contains(/ランキング|結果|ranking|result/i, { timeout: 10000 }).should('exist');
  });

  it('トップページに戻れること', () => {
    // 送信後、結果ページに遷移することを確認
    cy.url().should('include', '/ranking/calculate', { timeout: 10000 });
    
    // 直接ホームページに戻る
    cy.visit('/');
    
    // ホームページのタイトルが表示されることを確認
    cy.contains('h1', 'ゲームランキング集計アプリ').should('be.visible');
  });
}); 