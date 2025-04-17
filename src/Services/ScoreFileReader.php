<?php

namespace App\Services;

use App\Exceptions\InvalidFileFormatException;

class ScoreFileReader
{
    // 定数の定義
    private const HEADER_COUNT = 3;
    private const TIMESTAMP_INDEX = 0;
    private const PLAYER_ID_INDEX = 1;
    private const SCORE_INDEX = 2;

    /**
     * 実行環境を保持するプロパティ
     * testabilityのために外部からアクセス可能なprotectedにする
     */
    protected string $environment;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->environment = PHP_SAPI === 'cli' ? 'cli' : 'web';
    }

    /**
     * スコアファイルを読み込み、各行に対してコールバック関数を実行する
     *
     * @param string $filePath スコアファイルのパス
     * @param callable $callback コールバック関数 function(string $playerId, int $score): void
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    public function processScores(string $filePath, callable $callback): void
    {
        // 実行環境に応じて処理を分岐
        if ($this->environment === 'cli') {
            $this->processScoresCLI($filePath, $callback);
        } else {
            $this->processScoresWeb($filePath, $callback);
        }
    }

    /**
     * CLI環境でスコアファイルを読み込む処理
     *
     * @param string $filePath スコアファイルのパス
     * @param callable $callback コールバック関数
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function processScoresCLI(string $filePath, callable $callback): void
    {
        // ファイルが存在しない場合は例外をスロー
        if (!file_exists($filePath)) {
            throw new \Exception("ファイルが存在しません: {$filePath}");
        }

        $this->processScoresFromHandle(fopen($filePath, 'r'), $callback);
    }

    /**
     * Web環境でスコアファイルを読み込む処理
     *
     * @param string $filePath スコアファイルのパス
     * @param callable $callback コールバック関数
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function processScoresWeb(string $filePath, callable $callback): void
    {
        // ファイルが存在しない場合は例外をスロー
        if (!file_exists($filePath)) {
            throw new \Exception("ファイルが存在しません: {$filePath}");
        }

        $this->processScoresFromHandle(fopen($filePath, 'r'), $callback);
    }

    /**
     * ファイルハンドルからスコアを処理する共通処理
     *
     * @param resource $handle ファイルハンドル
     * @param callable $callback コールバック関数
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function processScoresFromHandle($handle, callable $callback): void
    {
        if ($handle === false) {
            throw new \Exception("ファイルを開けませんでした");
        }

        try {
            // ヘッダー行を読み込む
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new InvalidFileFormatException('ファイルが空です');
            }

            // ヘッダーの検証
            if (count($header) !== self::HEADER_COUNT || 
                $header[self::TIMESTAMP_INDEX] !== 'create_timestamp' || 
                $header[self::PLAYER_ID_INDEX] !== 'player_id' || 
                $header[self::SCORE_INDEX] !== 'score') {
                throw new InvalidFileFormatException('ヘッダーが不正です');
            }

            // データ行を読み込む
            while (($row = fgetcsv($handle)) !== false) {
                // 空行をスキップ
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                // 列数の検証
                if (count($row) !== self::HEADER_COUNT) {
                    throw new InvalidFileFormatException('列数が不正です');
                }

                $timestamp = $row[self::TIMESTAMP_INDEX];
                $playerId = $row[self::PLAYER_ID_INDEX];
                $score = $row[self::SCORE_INDEX];

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