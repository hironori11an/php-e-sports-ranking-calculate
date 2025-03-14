<?php

namespace App\Services;

use App\Exceptions\InvalidArgumentException;
use App\Exceptions\FileNotFoundException;

class ArgumentProcessor
{
    /**
     * コマンドライン引数を処理し、有効なファイルパスを返す
     *
     * @param array $argv コマンドライン引数の配列
     * @param int $argc コマンドライン引数の数
     * @return array [$entryFilePath, $scoreFilePath]
     * @throws InvalidArgumentException 引数の数が不正な場合
     * @throws FileNotFoundException ファイルが存在しない場合
     */
    public function processArguments(array $argv, int $argc): array
    {
        // コマンドライン引数のチェック
        if ($argc !== 3) {
            throw new InvalidArgumentException('入力引数の数が不正です。');
        }

        $entryFilePath = $argv[1];
        $scoreFilePath = $argv[2];

        // ファイルの存在チェック
        if (!file_exists($entryFilePath)) {
            throw new FileNotFoundException("エントリーファイルが見つかりません: {$entryFilePath}");
        }
        if (!file_exists($scoreFilePath)) {
            throw new FileNotFoundException("スコアファイルが見つかりません: {$scoreFilePath}");
        }

        return [$entryFilePath, $scoreFilePath];
    }
} 