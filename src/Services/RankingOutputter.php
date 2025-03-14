<?php

namespace App\Services;

class RankingOutputter
{
    /**
     * ランキングを標準出力に出力する
     *
     * @param array $rankings ランキング情報の配列
     * @throws \InvalidArgumentException ランキングデータの形式が不正な場合
     */
    public function outputRanking(array $rankings): void
    {
        // ヘッダー行を出力
        echo "rank,player_id,handle_name,score\n";

        // ランキングデータが空の場合は終了
        if (empty($rankings)) {
            return;
        }

        // ランキングデータを出力
        foreach ($rankings as $ranking) {
            // 必要なキーが存在するか確認
            if (!isset($ranking['rank']) || !isset($ranking['player_id']) || 
                !isset($ranking['handle_name']) || !isset($ranking['score'])) {
                throw new \InvalidArgumentException('ランキングデータの形式が不正です');
            }

            // CSV形式で出力
            echo sprintf("%d,%s,%s,%d\n",
                $ranking['rank'],
                $ranking['player_id'],
                $ranking['handle_name'],
                $ranking['score']
            );
        }
    }
} 