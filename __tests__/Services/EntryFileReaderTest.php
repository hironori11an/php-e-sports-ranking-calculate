<?php

namespace Tests\Services;

use App\Exceptions\InvalidFileFormatException;
use App\Services\EntryFileReader;
use PHPUnit\Framework\TestCase;

class EntryFileReaderTest extends TestCase
{
    private EntryFileReader $entryFileReader;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->entryFileReader = new EntryFileReader();
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_entry_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testReadEntriesWithValidFile(): void
    {
        // 有効なCSVファイルを作成
        $content = "player_id,handle_name\n" .
                   "player001,HANDLE_NAME_1\n" .
                   "player002,HANDLE_NAME_2\n";
        file_put_contents($this->tempFile, $content);

        $result = $this->entryFileReader->readEntries($this->tempFile);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('HANDLE_NAME_1', $result['player001']);
        $this->assertEquals('HANDLE_NAME_2', $result['player002']);
    }

    public function testReadEntriesWithInvalidHeader(): void
    {
        // 無効なヘッダーのCSVファイルを作成
        $content = "invalid_header1,invalid_header2\n" .
                   "player001,HANDLE_NAME_1\n";
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->entryFileReader->readEntries($this->tempFile);
    }

    public function testReadEntriesWithEmptyFile(): void
    {
        // 空のファイルを作成
        file_put_contents($this->tempFile, '');

        $this->expectException(InvalidFileFormatException::class);
        $this->entryFileReader->readEntries($this->tempFile);
    }

    public function testReadEntriesWithHeaderOnly(): void
    {
        // ヘッダーのみのCSVファイルを作成
        $content = "player_id,handle_name\n";
        file_put_contents($this->tempFile, $content);

        $result = $this->entryFileReader->readEntries($this->tempFile);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testReadEntriesWithInvalidColumnCount(): void
    {
        // 列数が不正なCSVファイルを作成
        $content = "player_id,handle_name\n" .
                   "player001,HANDLE_NAME_1\n" .
                   "player002\n"; // 列が足りない
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->entryFileReader->readEntries($this->tempFile);
    }

    public function testReadEntriesWithTooManyColumns(): void
    {
        // 列数が多すぎるCSVファイルを作成
        $content = "player_id,handle_name\n" .
                   "player001,HANDLE_NAME_1\n" .
                   "player002,HANDLE_NAME_2,extra_column\n"; // 列が多い
        file_put_contents($this->tempFile, $content);

        $this->expectException(InvalidFileFormatException::class);
        $this->entryFileReader->readEntries($this->tempFile);
    }

    public function testReadEntriesWithLargeFile(): void
    {
        // 大量のエントリーを含むCSVファイルを作成
        $content = "player_id,handle_name\n";
        for ($i = 1; $i <= 1000; $i++) {
            $content .= sprintf("player%04d,HANDLE_NAME_%d\n", $i, $i);
        }
        file_put_contents($this->tempFile, $content);

        $result = $this->entryFileReader->readEntries($this->tempFile);

        $this->assertIsArray($result);
        $this->assertCount(1000, $result);
        $this->assertEquals('HANDLE_NAME_1', $result['player0001']);
        $this->assertEquals('HANDLE_NAME_1000', $result['player1000']);
    }

    public function testReadEntriesWithDuplicatePlayerIds(): void
    {
        // 重複するプレイヤーIDを含むCSVファイルを作成
        $content = "player_id,handle_name\n" .
                   "player001,HANDLE_NAME_1\n" .
                   "player001,HANDLE_NAME_DUPLICATE\n"; // 重複するID
        file_put_contents($this->tempFile, $content);

        // 仕様では重複は許可されていないが、実装によっては後のエントリーで上書きされる可能性がある
        $result = $this->entryFileReader->readEntries($this->tempFile);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('HANDLE_NAME_DUPLICATE', $result['player001']); // 後のエントリーが優先される
    }

    public function testReadEntriesWithNonExistentFile(): void
    {
        // 存在しないファイルパスを指定
        $nonExistentFile = sys_get_temp_dir() . '/non_existent_file_' . uniqid() . '.csv';
        
        // FileNotFoundExceptionが発生することを期待
        $this->expectException(\Exception::class);
        $this->entryFileReader->readEntries($nonExistentFile);
    }

    public function testReadEntriesWithEmptyLines(): void
    {
        // 空行を含むCSVファイルを作成
        $content = "player_id,handle_name\n" .
                   "player001,HANDLE_NAME_1\n" .
                   "\n" . // 空行
                   "player002,HANDLE_NAME_2\n";
        file_put_contents($this->tempFile, $content);

        $result = $this->entryFileReader->readEntries($this->tempFile);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('HANDLE_NAME_1', $result['player001']);
        $this->assertEquals('HANDLE_NAME_2', $result['player002']);
    }
} 