<?php

namespace App\Services;

use App\Exceptions\InvalidFileFormatException;

class ScoreFileReader
{
    /**
     * スコアファイルを読み込み、各行に対してコールバック関数を実行する
     *
     * @param string $filePath スコアファイルのパス
     * @param callable $callback コールバック関数 function(string $playerId, int $score): void
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    public function processScores(string $filePath, callable $callback): void
    {
        // ファイルが存在しない場合は例外をスロー
        if (!file_exists($filePath)) {
            throw new \Exception("ファイルが存在しません: {$filePath}");
        }

        // ファイルを開く
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("ファイルを開けませんでした: {$filePath}");
        }

        try {
            // ヘッダー行を読み込む
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new InvalidFileFormatException('ファイルが空です');
            }

            // ヘッダーの検証
            if (count($header) !== 3 || 
                $header[0] !== 'create_timestamp' || 
                $header[1] !== 'player_id' || 
                $header[2] !== 'score') {
                throw new InvalidFileFormatException('ヘッダーが不正です');
            }

            // データ行を読み込む
            while (($row = fgetcsv($handle)) !== false) {
                // 空行をスキップ
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                // 列数の検証
                if (count($row) !== 3) {
                    throw new InvalidFileFormatException('列数が不正です');
                }

                $timestamp = $row[0];
                $playerId = $row[1];
                $score = $row[2];

                // タイムスタンプの形式を検証
                if (!$this->isValidTimestamp($timestamp)) {
                    throw new InvalidFileFormatException('タイムスタンプの形式が不正です');
                }

                // スコアが数値であることを検証
                if (!is_numeric($score)) {
                    throw new InvalidFileFormatException('スコアが数値ではありません');
                }

                // スコアが0以上であることを検証
                $score = (int)$score;
                if ($score < 0) {
                    throw new InvalidFileFormatException('スコアが負の値です');
                }

                // コールバック関数を呼び出す
                $callback($playerId, $score);
            }
        } finally {
            // ファイルを閉じる
            fclose($handle);
        }
    }

    /**
     * タイムスタンプの形式が有効かどうかを検証する
     *
     * @param string $timestamp タイムスタンプ
     * @return bool 有効な場合はtrue、そうでない場合はfalse
     */
    private function isValidTimestamp(string $timestamp): bool
    {
        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if (!preg_match($pattern, $timestamp)) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
        return $date !== false && $date->format('Y-m-d H:i:s') === $timestamp;
    }
} 