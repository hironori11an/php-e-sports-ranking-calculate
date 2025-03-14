<?php

namespace Tests\Services;

use App\Services\RankingCalculator;
use PHPUnit\Framework\TestCase;

class RankingCalculatorTest extends TestCase
{
    private RankingCalculator $rankingCalculator;

    protected function setUp(): void
    {
        $this->rankingCalculator = new RankingCalculator();
    }

    public function testUpdatePlayerScore(): void
    {
        // 初期スコアの設定
        $this->rankingCalculator->updatePlayerScore('player001', 100);
        
        // より高いスコアで更新
        $this->rankingCalculator->updatePlayerScore('player001', 200);
        
        // より低いスコアは無視される
        $this->rankingCalculator->updatePlayerScore('player001', 150);
        
        // 別のプレイヤーのスコア
        $this->rankingCalculator->updatePlayerScore('player002', 300);

        $entries = [
            'player001' => 'HANDLE_NAME_1',
            'player002' => 'HANDLE_NAME_2',
            'player003' => 'HANDLE_NAME_3', // スコアなし
        ];

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        $this->assertCount(2, $rankings);
        
        // ランキング順の確認
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals('player002', $rankings[0]['player_id']);
        $this->assertEquals('HANDLE_NAME_2', $rankings[0]['handle_name']);
        $this->assertEquals(300, $rankings[0]['score']);
        
        $this->assertEquals(2, $rankings[1]['rank']);
        $this->assertEquals('player001', $rankings[1]['player_id']);
        $this->assertEquals('HANDLE_NAME_1', $rankings[1]['handle_name']);
        $this->assertEquals(200, $rankings[1]['score']);
    }

    public function testCalculateRankingsWithSameScores(): void
    {
        $this->rankingCalculator->updatePlayerScore('player001', 100);
        $this->rankingCalculator->updatePlayerScore('player002', 100);
        $this->rankingCalculator->updatePlayerScore('player003', 50);

        $entries = [
            'player001' => 'HANDLE_NAME_1',
            'player002' => 'HANDLE_NAME_2',
            'player003' => 'HANDLE_NAME_3',
        ];

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        $this->assertCount(3, $rankings);
        
        // 同点の場合、同じランクになる
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals('player001', $rankings[0]['player_id']);
        $this->assertEquals(100, $rankings[0]['score']);
        
        $this->assertEquals(1, $rankings[1]['rank']);
        $this->assertEquals('player002', $rankings[1]['player_id']);
        $this->assertEquals(100, $rankings[1]['score']);
        
        $this->assertEquals(3, $rankings[2]['rank']);
        $this->assertEquals('player003', $rankings[2]['player_id']);
        $this->assertEquals(50, $rankings[2]['score']);
    }

    public function testCalculateRankingsWithNoScores(): void
    {
        $entries = [
            'player001' => 'HANDLE_NAME_1',
            'player002' => 'HANDLE_NAME_2',
        ];

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        $this->assertEmpty($rankings);
    }

    public function testCalculateRankingsWithTop10Only(): void
    {
        // 11人分のスコアを設定
        for ($i = 1; $i <= 11; $i++) {
            $playerId = sprintf('player%03d', $i);
            $score = 1000 - $i * 10; // 990, 980, 970, ...
            $this->rankingCalculator->updatePlayerScore($playerId, $score);
        }

        $entries = [];
        for ($i = 1; $i <= 11; $i++) {
            $playerId = sprintf('player%03d', $i);
            $entries[$playerId] = sprintf('HANDLE_NAME_%d', $i);
        }

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        // 上位10位までのみ返される
        $this->assertCount(10, $rankings);
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals(990, $rankings[0]['score']);
        $this->assertEquals(10, $rankings[9]['rank']);
        $this->assertEquals(900, $rankings[9]['score']);
    }

    public function testCalculateRankingsWithNonEntryPlayers(): void
    {
        // エントリーにないプレイヤーのスコアを設定
        $this->rankingCalculator->updatePlayerScore('player001', 100);
        $this->rankingCalculator->updatePlayerScore('player002', 200);
        $this->rankingCalculator->updatePlayerScore('non_entry_player', 300); // エントリーにない

        $entries = [
            'player001' => 'HANDLE_NAME_1',
            'player002' => 'HANDLE_NAME_2',
        ];

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        // エントリーにないプレイヤーのスコアは無視される
        $this->assertCount(2, $rankings);
        $this->assertEquals('player002', $rankings[0]['player_id']);
        $this->assertEquals(200, $rankings[0]['score']);
        $this->assertEquals('player001', $rankings[1]['player_id']);
        $this->assertEquals(100, $rankings[1]['score']);
    }

    public function testCalculateRankingsWithSameScoresSortedByPlayerId(): void
    {
        // 同点のスコアを設定（プレイヤーIDが逆順）
        $this->rankingCalculator->updatePlayerScore('player003', 100);
        $this->rankingCalculator->updatePlayerScore('player002', 100);
        $this->rankingCalculator->updatePlayerScore('player001', 100);

        $entries = [
            'player001' => 'HANDLE_NAME_1',
            'player002' => 'HANDLE_NAME_2',
            'player003' => 'HANDLE_NAME_3',
        ];

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        // 同点の場合、プレイヤーIDでソートされる
        $this->assertCount(3, $rankings);
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals('player001', $rankings[0]['player_id']);
        
        $this->assertEquals(1, $rankings[1]['rank']);
        $this->assertEquals('player002', $rankings[1]['player_id']);
        
        $this->assertEquals(1, $rankings[2]['rank']);
        $this->assertEquals('player003', $rankings[2]['player_id']);
    }

    public function testCalculateRankingsWithMoreThan10SameRankPlayers(): void
    {
        // 11人全員が同じスコア
        for ($i = 1; $i <= 11; $i++) {
            $playerId = sprintf('player%03d', $i);
            $this->rankingCalculator->updatePlayerScore($playerId, 100);
        }

        $entries = [];
        for ($i = 1; $i <= 11; $i++) {
            $playerId = sprintf('player%03d', $i);
            $entries[$playerId] = sprintf('HANDLE_NAME_%d', $i);
        }

        $rankings = $this->rankingCalculator->calculateRankings($entries);

        // 同じランクの場合、10位以上も含まれる
        $this->assertGreaterThan(10, count($rankings));
        $this->assertEquals(1, $rankings[0]['rank']);
        $this->assertEquals(1, $rankings[10]['rank']);
    }
} 