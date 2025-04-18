describe('ホームページ', () => {
  beforeEach(() => {
    // テスト実行前にホームページを訪問
    cy.visit('/');
  });

  it('ページが正しく表示されること', () => {
    // タイトルが表示されることを確認
    cy.contains('h1', 'ゲームランキング集計アプリ').should('be.visible');
    
    // フォームが表示されることを確認
    cy.get('form').should('be.visible');
    
    // エントリーファイル入力が存在することを確認
    cy.get('#entry-file').should('exist');
    
    // スコアファイル入力が存在することを確認
    cy.get('#score-file').should('exist');
    
    // 送信ボタンが表示されることを確認
    cy.contains('button', 'ランキング計算').should('be.visible');
  });
}); 