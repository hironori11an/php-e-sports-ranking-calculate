<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Action\Ranking\RankingFormAction;

require __DIR__ . '/../vendor/autoload.php';

// DIコンテナの設定
$container = App\Container::build();

// アプリケーションの作成
$app = AppFactory::createFromContainer($container);

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// アップロードサイズ制限の設定
$app->addBodyParsingMiddleware();

// アップロードディレクトリの設定
$container->set(Twig::class, function () {
    return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
});

// ルーティングの設定
$app->get('/', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.html.twig');
});

// フォーム送信用ルート
$app->post('/ranking/calculate', RankingFormAction::class);

// エラーハンドリングの設定
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->run();