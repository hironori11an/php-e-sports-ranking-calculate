<?php

namespace App\Services;

class RankingCalculator
{
    /**
     * プレイヤーの最高スコアを保持する配列
     *
     * @var array
     */
    private array $playerScores = [];

    /**
     * プレイヤーの最高スコアを更新する
     *
     * @param string $playerId プレイヤーID
     * @param int $score スコア
     */
    public function updatePlayerScore(string $playerId, int $score): void
    {
        // 初めてのスコア登録、または既存のスコアより高い場合のみ更新
        if (!isset($this->playerScores[$playerId]) || $score > $this->playerScores[$playerId]) {
            $this->playerScores[$playerId] = $score;
        }
    }

    /**
     * ランキングを計算する
     *
     * @param array $entries プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @return array ランキング情報の配列
     */
    public function calculateRankings(array $entries): array
    {
        // プレイヤーのスコアがない場合は空の配列を返す
        if (empty($this->playerScores)) {
            return [];
        }

        // エントリーに登録されているプレイヤーのスコアのみを抽出
        $validScores = [];
        foreach ($this->playerScores as $playerId => $score) {
            if (isset($entries[$playerId])) {
                $validScores[$playerId] = $score;
            }
        }

        // スコアがない場合は空の配列を返す
        if (empty($validScores)) {
            return [];
        }

        // スコアの降順でソート
        arsort($validScores);

        // ランキング情報を作成
        $rankings = [];
        $rank = 1;
        $prevScore = null;
        $count = 0;
        $sameRankPlayers = [];

        foreach ($validScores as $playerId => $score) {
            // 前のスコアと異なる場合、ランクを更新
            if ($prevScore !== null && $score < $prevScore) {
                // 同じランクのプレイヤーをプレイヤーIDでソートして追加
                if (!empty($sameRankPlayers)) {
                    ksort($sameRankPlayers);
                    foreach ($sameRankPlayers as $pid => $data) {
                        $rankings[] = [
                            'rank' => $rank,
                            'player_id' => $pid,
                            'handle_name' => $data['handle_name'],
                            'score' => $data['score']
                        ];
                        $count++;
                    }
                    $sameRankPlayers = [];
                }
                $rank = $count + 1;
            }

            // 上位10位までのプレイヤーを含むまで処理
            // 同点の場合は10位以上も含める
            if ($count >= 10 && $prevScore !== null && $score < $prevScore) {
                break;
            }

            // 現在のプレイヤーを同じランクのプレイヤーリストに追加
            $sameRankPlayers[$playerId] = [
                'handle_name' => $entries[$playerId],
                'score' => $score
            ];

            $prevScore = $score;
        }

        // 残りの同じランクのプレイヤーを追加
        if (!empty($sameRankPlayers)) {
            ksort($sameRankPlayers);
            foreach ($sameRankPlayers as $playerId => $data) {
                $rankings[] = [
                    'rank' => $rank,
                    'player_id' => $playerId,
                    'handle_name' => $data['handle_name'],
                    'score' => $data['score']
                ];
            }
        }


        return $rankings;
    }
} 