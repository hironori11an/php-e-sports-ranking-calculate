<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Action\Home\HomeAction;
require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    
    return $view->render($response, 'home.html.twig', [
        'name' => 'John',
    ]);
});

// API用のルートグループ
$app->group('/api', function ($group) {
    $group->get('/', HomeAction::class);
    // // APIエンドポイント
    // $group->get('/users', function (Request $request, Response $response, $args) {
    //     $users = [
    //         ['id' => 1, 'name' => 'ユーザー1'],
    //         ['id' => 2, 'name' => 'ユーザー2']
    //     ];
    //     $payload = json_encode($users);
    //     $response->getBody()->write($payload);
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
    
    // $group->get('/users/{id}', function (Request $request, Response $response, $args) {
    //     $id = $args['id'];
    //     $user = ['id' => $id, 'name' => 'ユーザー' . $id];
    //     $payload = json_encode($user);
    //     $response->getBody()->write($payload);
    //     return $response->withHeader('Content-Type', 'application/json');
    // });
});

$app->run();