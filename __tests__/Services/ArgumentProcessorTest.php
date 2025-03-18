<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\ArgumentProcessor;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\FileNotFoundException;

class ArgumentProcessorTest extends TestCase
{
    private ArgumentProcessor $argumentProcessor;
    private string $tempDir;
    private string $entryFilePath;
    private string $scoreFilePath;

    protected function setUp(): void
    {
        $this->argumentProcessor = new ArgumentProcessor();
        
        // 一時ディレクトリとテスト用ファイルの作成
        $this->tempDir = sys_get_temp_dir() . '/argument_processor_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        
        $this->entryFilePath = $this->tempDir . '/entry.csv';
        $this->scoreFilePath = $this->tempDir . '/score.csv';
        
        // テスト用の空ファイルを作成
        file_put_contents($this->entryFilePath, '');
        file_put_contents($this->scoreFilePath, '');
    }

    protected function tearDown(): void
    {
        // テスト用ファイルとディレクトリの削除
        if (file_exists($this->entryFilePath)) {
            unlink($this->entryFilePath);
        }
        if (file_exists($this->scoreFilePath)) {
            unlink($this->scoreFilePath);
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }

    /**
     * 正常系：正しい引数が渡された場合のテスト
     */
    public function testProcessArgumentsWithValidArguments(): void
    {
        $argv = ['script.php', $this->entryFilePath, $this->scoreFilePath];
        $argc = 3;

        $result = $this->argumentProcessor->processArguments($argv, $argc);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($this->entryFilePath, $result[0]);
        $this->assertEquals($this->scoreFilePath, $result[1]);
    }

    /**
     * 異常系：引数の数が不正な場合のテスト
     */
    public function testProcessArgumentsWithInvalidArgumentCount(): void
    {
        // 引数が少ない場合
        $argv = ['script.php', $this->entryFilePath];
        $argc = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ほげふがほげ入力引数の数が不正です。');
        
        $this->argumentProcessor->processArguments($argv, $argc);
    }

    /**
     * 異常系：引数の数が多い場合のテスト
     */
    public function testProcessArgumentsWithTooManyArguments(): void
    {
        // 引数が多い場合
        $argv = ['script.php', $this->entryFilePath, $this->scoreFilePath, 'extra_arg'];
        $argc = 4;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('入力引数の数が不正です。');
        
        $this->argumentProcessor->processArguments($argv, $argc);
    }

    /**
     * 異常系：エントリーファイルが存在しない場合のテスト
     */
    public function testProcessArgumentsWithNonExistentEntryFile(): void
    {
        $nonExistentFile = $this->tempDir . '/non_existent_entry.csv';
        $argv = ['script.php', $nonExistentFile, $this->scoreFilePath];
        $argc = 3;

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("エントリーファイルが見つかりません: {$nonExistentFile}");
        
        $this->argumentProcessor->processArguments($argv, $argc);
    }

    /**
     * 異常系：スコアファイルが存在しない場合のテスト
     */
    public function testProcessArgumentsWithNonExistentScoreFile(): void
    {
        $nonExistentFile = $this->tempDir . '/non_existent_score.csv';
        $argv = ['script.php', $this->entryFilePath, $nonExistentFile];
        $argc = 3;

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("スコアファイルが見つかりません: {$nonExistentFile}");
        
        $this->argumentProcessor->processArguments($argv, $argc);
    }
} 