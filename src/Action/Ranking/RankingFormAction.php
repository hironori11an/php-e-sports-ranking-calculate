<?php

namespace App\Action\Ranking;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use App\Services\EntryFileReader;
use App\Services\ScoreFileReader;
use App\Services\RankingCalculator;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidFileFormatException;

final class RankingFormAction
{
    private EntryFileReader $entryFileReader;
    private ScoreFileReader $scoreFileReader;
    private RankingCalculator $rankingCalculator;
    private Twig $twig;

    public function __construct(
        EntryFileReader $entryFileReader,
        ScoreFileReader $scoreFileReader,
        RankingCalculator $rankingCalculator,
        Twig $twig
    ) {
        $this->entryFileReader = $entryFileReader;
        $this->scoreFileReader = $scoreFileReader;
        $this->rankingCalculator = $rankingCalculator;
        $this->twig = $twig;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        
        // アップロードされたファイルの取得
        $entryFile = $uploadedFiles['entry_file'] ?? null;
        $scoreFile = $uploadedFiles['score_file'] ?? null;
        
        // ファイルの有無をチェック
        if (!$entryFile || !$scoreFile) {
            return $this->twig->render($response, 'result.html.twig', [
                'error' => 'ファイルがアップロードされていません。両方のファイルを選択してください。'
            ]);
        }
        
        // アップロードエラーのチェック
        if ($entryFile->getError() !== UPLOAD_ERR_OK) {
            return $this->twig->render($response, 'result.html.twig', [
                'error' => 'エントリーファイルのアップロードに失敗しました: ' . $this->getUploadErrorMessage($entryFile->getError())
            ]);
        }
        
        if ($scoreFile->getError() !== UPLOAD_ERR_OK) {
            return $this->twig->render($response, 'result.html.twig', [
                'error' => 'スコアファイルのアップロードに失敗しました: ' . $this->getUploadErrorMessage($scoreFile->getError())
            ]);
        }
        
        try {
            // 一時ファイルとして保存
            $tmpEntryPath = sys_get_temp_dir() . '/' . uniqid('entry_', true) . '.csv';
            $tmpScorePath = sys_get_temp_dir() . '/' . uniqid('score_', true) . '.csv';
            
            // アップロードされたファイルを一時ファイルに移動
            $entryFile->moveTo($tmpEntryPath);
            $scoreFile->moveTo($tmpScorePath);
            
            // エントリーファイルの読み込み
            $entries = $this->entryFileReader->readEntries($tmpEntryPath);
            
            // スコアファイルをストリーム処理して、エントリー済みプレイヤーの最高スコアを計算
            $this->scoreFileReader->processScores($tmpScorePath, function ($playerId, $score) use ($entries) {
                // エントリー済みプレイヤーのスコアのみを処理
                if (isset($entries[$playerId])) {
                    $this->rankingCalculator->updatePlayerScore($playerId, $score);
                }
            });
            
            // ランキングの計算
            $rankings = $this->rankingCalculator->calculateRankings($entries);
            
            // 一時ファイルを削除
            @unlink($tmpEntryPath);
            @unlink($tmpScorePath);
            
            // テンプレートでレンダリング
            return $this->twig->render($response, 'result.html.twig', [
                'rankings' => $rankings
            ]);
            
        } catch (InvalidArgumentException | FileNotFoundException | InvalidFileFormatException $e) {
            // 一時ファイルを削除
            if (isset($tmpEntryPath) && file_exists($tmpEntryPath)) {
                @unlink($tmpEntryPath);
            }
            if (isset($tmpScorePath) && file_exists($tmpScorePath)) {
                @unlink($tmpScorePath);
            }
            
            // エラーメッセージをテンプレートで表示
            return $this->twig->render($response, 'result.html.twig', [
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // 一時ファイルを削除
            if (isset($tmpEntryPath) && file_exists($tmpEntryPath)) {
                @unlink($tmpEntryPath);
            }
            if (isset($tmpScorePath) && file_exists($tmpScorePath)) {
                @unlink($tmpScorePath);
            }
            
            // 予期せぬエラーの場合
            return $this->twig->render($response, 'result.html.twig', [
                'error' => 'エラーが発生しました: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * アップロードエラーコードに対応するメッセージを取得
     */
    private function getUploadErrorMessage(int $error): string
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'ファイルサイズがPHPの制限を超えています';
            case UPLOAD_ERR_FORM_SIZE:
                return 'ファイルサイズがフォームの制限を超えています';
            case UPLOAD_ERR_PARTIAL:
                return 'ファイルの一部のみがアップロードされました';
            case UPLOAD_ERR_NO_FILE:
                return 'ファイルがアップロードされていません';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '一時フォルダがありません';
            case UPLOAD_ERR_CANT_WRITE:
                return 'ディスクへの書き込みに失敗しました';
            case UPLOAD_ERR_EXTENSION:
                return 'PHPの拡張機能によってアップロードが中止されました';
            default:
                return '不明なエラーが発生しました';
        }
    }
} 