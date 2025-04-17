<?php

namespace App;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use App\Services\EntryFileReader;
use App\Services\ScoreFileReader;
use App\Services\RankingCalculator;
use App\Services\RankingOutputter;
use App\Services\ArgumentProcessor;
use App\Action\Ranking\RankingFormAction;

class Container
{
    public static function build()
    {
        $containerBuilder = new ContainerBuilder();
        
        // キャッシュを有効にしたい場合はコメントを外す
        // $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
        
        // より明示的な依存関係の定義
        $containerBuilder->addDefinitions([
            // 引数処理
            ArgumentProcessor::class => \DI\autowire(),
            
            // ファイル読み込み
            EntryFileReader::class => \DI\autowire(),
            ScoreFileReader::class => \DI\autowire(),
            
            // ランキング関連
            RankingCalculator::class => \DI\autowire(),
            RankingOutputter::class => \DI\autowire(),
            
            // Webアクション
            RankingFormAction::class => \DI\autowire(),
        ]);
        
        return $containerBuilder->build();
    }
} 