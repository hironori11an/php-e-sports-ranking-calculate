<?php

namespace Tests\Services;

use App\Exceptions\InvalidFileFormatException;
use App\Services\ScoreFileReader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ScoreFileReaderTest extends TestCase
{
    private ScoreFileReader $scoreFileReader;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->scoreFileReader = new ScoreFileReader();
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_score_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function createScoreFileReaderWithMockedEnvironment(string $env): ScoreFileReader
    {
        $reader = new ScoreFileReader();
        
        // 環境をモックするためにリフレクションを使用
        $reflectionClass = new ReflectionClass($reader);
        $reflectionProperty = $reflectionClass->getProperty('environment');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($reader, $env);
        
        return $reader;
    }

    public function testProcessScoresWithValidFileInCliEnvironment(): void
    {
        // CLIモックスコアリーダーを作成
        $scoreFileReader = $this->createScoreFileReaderWithMockedEnvironment('cli');
        
        // 有効なCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player001,200\n" .
                   "2023-01-01 12:02:00,player002,150\n";
        file_put_contents($this->tempFile, $content);

        $results = [];
        $callback = function ($playerId, $score) use (&$results) {
            if (!isset($results[$playerId]) || $score > $results[$playerId]) {
                $results[$playerId] = $score;
            }
        };

        $scoreFileReader->processScores($this->tempFile, $callback);

        $this->assertCount(2, $results);
        $this->assertEquals(200, $results['player001']);
        $this->assertEquals(150, $results['player002']);
    }

    public function testProcessScoresWithValidFileInWebEnvironment(): void
    {
        // Webモックスコアリーダーを作成
        $scoreFileReader = $this->createScoreFileReaderWithMockedEnvironment('web');
        
        // 有効なCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player001,200\n" .
                   "2023-01-01 12:02:00,player002,150\n";
        file_put_contents($this->tempFile, $content);

        $results = [];
        $callback = function ($playerId, $score) use (&$results) {
            if (!isset($results[$playerId]) || $score > $results[$playerId]) {
                $results[$playerId] = $score;
            }
        };

        $scoreFileReader->processScores($this->tempFile, $callback);

        $this->assertCount(2, $results);
        $this->assertEquals(200, $results['player001']);
        $this->assertEquals(150, $results['player002']);
    }

    public function testProcessScoresWithInvalidHeader(): void
    {
        // 無効なヘッダーのCSVファイルを作成
        $content = "invalid_header1,invalid_header2,invalid_header3\n" .
                   "2023-01-01 12:00:00,player001,100\n";
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithEmptyFile(): void
    {
        // 空のファイルを作成
        file_put_contents($this->tempFile, '');

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithHeaderOnly(): void
    {
        // ヘッダーのみのCSVファイルを作成
        $content = "create_timestamp,player_id,score\n";
        file_put_contents($this->tempFile, $content);

        $called = false;
        $callback = function () use (&$called) {
            $called = true;
        };

        $this->scoreFileReader->processScores($this->tempFile, $callback);

        $this->assertFalse($called);
    }

    public function testProcessScoresWithInvalidColumnCount(): void
    {
        // 列数が不正なCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player002\n"; // 列が足りない
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithTooManyColumns(): void
    {
        // 列数が多すぎるCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player002,150,extra_column\n"; // 列が多い
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithLargeFile(): void
    {
        // 大量のスコアデータを含むCSVファイルを作成
        $content = "create_timestamp,player_id,score\n";
        for ($i = 1; $i <= 1000; $i++) {
            $timestamp = sprintf("2023-01-01 %02d:%02d:%02d", rand(0, 23), rand(0, 59), rand(0, 59));
            $playerId = sprintf("player%04d", rand(1, 100)); // 100人のプレイヤーからランダムに選択
            $score = rand(1, 1000);
            $content .= "{$timestamp},{$playerId},{$score}\n";
        }
        file_put_contents($this->tempFile, $content);

        $playerScores = [];
        $callback = function ($playerId, $score) use (&$playerScores) {
            if (!isset($playerScores[$playerId]) || $score > $playerScores[$playerId]) {
                $playerScores[$playerId] = $score;
            }
        };

        $this->scoreFileReader->processScores($this->tempFile, $callback);

        // 少なくとも1人以上のプレイヤーのスコアが記録されているはず
        $this->assertGreaterThan(0, count($playerScores));
        // 最大でも100人のプレイヤーしかいないはず
        $this->assertLessThanOrEqual(100, count($playerScores));
    }

    public function testProcessScoresWithNonExistentFile(): void
    {
        // 存在しないファイルパスを指定
        $nonExistentFile = sys_get_temp_dir() . '/non_existent_file_' . uniqid() . '.csv';
        
        // 例外が発生することを期待
        $this->expectException(\Exception::class);
        $this->scoreFileReader->processScores($nonExistentFile, function () {});
    }

    public function testProcessScoresWithEmptyLines(): void
    {
        // 空行を含むCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "\n" . // 空行
                   "2023-01-01 12:02:00,player002,150\n";
        file_put_contents($this->tempFile, $content);

        $results = [];
        $callback = function ($playerId, $score) use (&$results) {
            $results[$playerId] = $score;
        };

        $this->scoreFileReader->processScores($this->tempFile, $callback);

        $this->assertCount(2, $results);
        $this->assertEquals(100, $results['player001']);
        $this->assertEquals(150, $results['player002']);
    }

    public function testProcessScoresWithInvalidScoreFormat(): void
    {
        // スコアが数値でないCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player002,invalid_score\n"; // スコアが数値でない
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithNegativeScore(): void
    {
        // 負のスコアを含むCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "2023-01-01 12:01:00,player002,-50\n"; // 負のスコア
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }

    public function testProcessScoresWithInvalidTimestampFormat(): void
    {
        // タイムスタンプの形式が不正なCSVファイルを作成
        $content = "create_timestamp,player_id,score\n" .
                   "2023-01-01 12:00:00,player001,100\n" .
                   "invalid_timestamp,player002,150\n"; // タイムスタンプの形式が不正
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->scoreFileReader->processScores($this->tempFile, function () {});
    }
} 