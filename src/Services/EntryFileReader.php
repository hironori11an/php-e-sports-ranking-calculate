<?php

namespace App\Services;

use App\Exceptions\InvalidFileFormatException;

class EntryFileReader
{
    // 定数の定義
    private const PLAYER_ID_INDEX = 0;
    private const HANDLE_NAME_INDEX = 1;

    /**
     * エントリーファイルを読み込み、プレイヤーIDをキー、ハンドルネームを値とする連想配列を返す
     *
     * @param string $filePath エントリーファイルのパス
     * @return array プレイヤーIDをキー、ハンドルネームを値とする連想配列
     * @throws InvalidFileFormatException ファイル形式が不正な場合
     */
    public function readEntries(string $filePath): array
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