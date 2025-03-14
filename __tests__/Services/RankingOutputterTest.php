<?php

namespace Tests\Services;

use App\Services\RankingOutputter;
use PHPUnit\Framework\TestCase;

class RankingOutputterTest extends TestCase
{
    private RankingOutputter $rankingOutputter;

    protected function setUp(): void
    {
        $this->rankingOutputter = new RankingOutputter();
    }

    public function testOutputRankingWithValidData(): void
    {
        $rankings = [
            [
                'rank' => 1,
                'player_id' => 'player001',
                'handle_name' => 'HANDLE_NAME_1',
                'score' => 1000
            ],
            [
                'rank' => 2,
                'player_id' => 'player002',
                'handle_name' => 'HANDLE_NAME_2',
                'score' => 900
            ],
            [
                'rank' => 3,
                'player_id' => 'player003',
                'handle_name' => 'HANDLE_NAME_3',
                'score' => 800
            ]
        ];

        // 標準出力をキャプチャ
        ob_start();
        $this->rankingOutputter->outputRanking($rankings);
        $output = ob_get_clean();

        // 期待される出力
        $expected = "rank,player_id,handle_name,score\n" .
                    "1,player001,HANDLE_NAME_1,1000\n" .
                    "2,player002,HANDLE_NAME_2,900\n" .
                    "3,player003,HANDLE_NAME_3,800\n";

        $this->assertEquals($expected, $output);
    }

    public function testOutputRankingWithEmptyData(): void
    {
        // 空のランキングデータ
        $rankings = [];

        // 標準出力をキャプチャ
        ob_start();
        $this->rankingOutputter->outputRanking($rankings);
        $output = ob_get_clean();

        // ヘッダーのみが出力される
        $expected = "rank,player_id,handle_name,score\n";

        $this->assertEquals($expected, $output);
    }

    public function testOutputRankingWithSameRanks(): void
    {
        $rankings = [
            [
                'rank' => 1,
                'player_id' => 'player001',
                'handle_name' => 'HANDLE_NAME_1',
                'score' => 1000
            ],
            [
                'rank' => 1,
                'player_id' => 'player002',
                'handle_name' => 'HANDLE_NAME_2',
                'score' => 1000
            ],
            [
                'rank' => 3,
                'player_id' => 'player003',
                'handle_name' => 'HANDLE_NAME_3',
                'score' => 900
            ]
        ];

        // 標準出力をキャプチャ
        ob_start();
        $this->rankingOutputter->outputRanking($rankings);
        $output = ob_get_clean();

        // 期待される出力
        $expected = "rank,player_id,handle_name,score\n" .
                    "1,player001,HANDLE_NAME_1,1000\n" .
                    "1,player002,HANDLE_NAME_2,1000\n" .
                    "3,player003,HANDLE_NAME_3,900\n";

        $this->assertEquals($expected, $output);
    }

    public function testOutputRankingWithLargeData(): void
    {
        // 大量のランキングデータを作成
        $rankings = [];
        for ($i = 1; $i <= 10; $i++) {
            $rankings[] = [
                'rank' => $i,
                'player_id' => sprintf('player%03d', $i),
                'handle_name' => sprintf('HANDLE_NAME_%d', $i),
                'score' => 1000 - ($i - 1) * 10
            ];
        }

        // 標準出力をキャプチャ
        ob_start();
        $this->rankingOutputter->outputRanking($rankings);
        $output = ob_get_clean();

        // 期待される出力
        $expected = "rank,player_id,handle_name,score\n";
        for ($i = 1; $i <= 10; $i++) {
            $expected .= sprintf("%d,player%03d,HANDLE_NAME_%d,%d\n", 
                $i, $i, $i, 1000 - ($i - 1) * 10);
        }

        $this->assertEquals($expected, $output);
    }

    public function testOutputRankingWithSpecialCharacters(): void
    {
        // 特殊文字を含むランキングデータ
        $rankings = [
            [
                'rank' => 1,
                'player_id' => 'player_001',
                'handle_name' => 'HANDLE_NAME_1',
                'score' => 1000
            ],
            [
                'rank' => 2,
                'player_id' => 'player_002',
                'handle_name' => 'HANDLE_NAME_2',
                'score' => 900
            ]
        ];

        // 標準出力をキャプチャ
        ob_start();
        $this->rankingOutputter->outputRanking($rankings);
        $output = ob_get_clean();

        // 期待される出力
        $expected = "rank,player_id,handle_name,score\n" .
                    "1,player_001,HANDLE_NAME_1,1000\n" .
                    "2,player_002,HANDLE_NAME_2,900\n";

        $this->assertEquals($expected, $output);
    }

    public function testOutputRankingWithInvalidData(): void
    {
        // 無効なランキングデータ（必要なキーが不足）
        $rankings = [
            [
                'rank' => 1,
                'player_id' => 'player001',
                // handle_nameキーが不足
                'score' => 1000
            ]
        ];

        // 例外が発生することを期待
        $this->expectException(\InvalidArgumentException::class);
        
        try {
            // 標準出力をキャプチャ
            ob_start();
            $this->rankingOutputter->outputRanking($rankings);
        } finally {
            // 必ず出力バッファを閉じる
            ob_end_clean();
        }
    }
} 