<?php

namespace App\Services;

use App\Exceptions\InvalidFileFormatException;

class EntryFileReader
{
    // 定数の定義
    private const PLAYER_ID_INDEX = 0;
    private const HANDLE_NAME_INDEX = 1;

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
     * エントリーファイルを読み込み、プレイヤーIDをキー、ハンドルネームを値とする連想配列を返す
     *
     * @param string $filePath エントリーファイルのパス
     * @return array プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    public function readEntries(string $filePath): array
    {
        // 実行環境に応じて処理を分岐
        if ($this->environment === 'cli') {
            return $this->readEntriesCLI($filePath);
        } else {
            return $this->readEntriesWeb($filePath);
        }
    }

    /**
     * CLI環境でエントリーファイルを読み込む処理
     *
     * @param string $filePath エントリーファイルのパス
     * @return array プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function readEntriesCLI(string $filePath): array
    {
        // ファイルが存在しない場合は例外をスロー
        if (!file_exists($filePath)) {
            throw new \Exception("ファイルが存在しません: {$filePath}");
        }

        return $this->readEntriesFromHandle(fopen($filePath, 'r'));
    }

    /**
     * Web環境でエントリーファイルを読み込む処理
     *
     * @param string $filePath エントリーファイルのパス
     * @return array プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function readEntriesWeb(string $filePath): array
    {
        // ファイルが存在しない場合は例外をスロー
        if (!file_exists($filePath)) {
            throw new \Exception("ファイルが存在しません: {$filePath}");
        }

        return $this->readEntriesFromHandle(fopen($filePath, 'r'));
    }

    /**
     * ファイルハンドルからエントリーを読み込む共通処理
     *
     * @param resource $handle ファイルハンドル
     * @return array プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    private function readEntriesFromHandle($handle): array
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
            if (count($header) !== 2 || $header[self::PLAYER_ID_INDEX] !== 'player_id' || $header[self::HANDLE_NAME_INDEX] !== 'handle_name') {
                throw new InvalidFileFormatException('ヘッダーが不正です');
            }

            $entries = [];

            // データ行を読み込む
            while (($row = fgetcsv($handle)) !== false) {
                // 空行をスキップ
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                // 列数の検証
                if (count($row) !== 2) {
                    throw new InvalidFileFormatException('列数が不正です');
                }

                $playerId = $row[self::PLAYER_ID_INDEX];
                $handleName = $row[self::HANDLE_NAME_INDEX];

                // プレイヤーIDとハンドルネームを連想配列に追加
                $entries[$playerId] = $handleName;
            }

            return $entries;
        } finally {
            // ファイルを閉じる
            fclose($handle);
        }
    }
} 