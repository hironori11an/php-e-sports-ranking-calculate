#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Container;
use App\Services\EntryFileReader;
use App\Services\ScoreFileReader;
use App\Services\RankingCalculator;
use App\Services\RankingOutputter;
use App\Services\ArgumentProcessor;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidFileFormatException;

// メイン処理
try {
    // DIコンテナの初期化
    $container = Container::build();
    
    // 引数の処理
    $argumentProcessor = $container->get(ArgumentProcessor::class);
    list($entryFilePath, $scoreFilePath) = $argumentProcessor->processArguments($argv, $argc);

    // エントリーファイルの読み込み
    $entryFileReader = $container->get(EntryFileReader::class);
    $entries = $entryFileReader->readEntries($entryFilePath);

    // スコアファイルの読み込みとランキング計算
    $scoreFileReader = $container->get(ScoreFileReader::class);
    $rankingCalculator = $container->get(RankingCalculator::class);
    
    // スコアファイルをストリーム処理して、エントリー済みプレイヤーの最高スコアを計算
    $scoreFileReader->processScores($scoreFilePath, function ($playerId, $score) use ($rankingCalculator, $entries) {
        // エントリー済みプレイヤーのスコアのみを処理
        if (isset($entries[$playerId])) {
            $rankingCalculator->updatePlayerScore($playerId, $score);
        }
    });

    // ランキングの計算と出力
    $rankings = $rankingCalculator->calculateRankings($entries);
    
    // ランキングの出力
    $outputter = $container->get(RankingOutputter::class);
    $outputter->outputRanking($rankings);

    exit(0);
} catch (InvalidArgumentException | FileNotFoundException | InvalidFileFormatException $e) {
    // エラーメッセージを標準エラー出力に出力
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (Exception $e) {
    // 予期せぬエラーの場合
    fwrite(STDERR, "エラーが発生しました: " . $e->getMessage() . PHP_EOL);
    exit(1);
} 