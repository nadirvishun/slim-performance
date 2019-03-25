<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
//月考核展示
$app->get('/index/month', '\app\controllers\IndexController:month')->setName('month');
//年考核展示
$app->get('/index/year', '\app\controllers\IndexController:year')->setName('year');
//月考核详情
$app->map(['get', 'post'], '/index/month-info', '\app\controllers\IndexController:monthInfo')->setName('month-info');
//月记录提交
$app->post('/index/month-submit', '\app\controllers\IndexController:monthSubmit')->setName('month-submit');
//行为分提交
$app->post('/index/behavior-submit', '\app\controllers\IndexController:behaviorSubmit')->setName('behavior-submit');
//行为分删除
$app->post('/index/behavior-delete', '\app\controllers\IndexController:behaviorDelete')->setName('behavior-delete');
//获取用户信息
$app->post('/index/get-user-info', '\app\controllers\IndexController:getUserInfo')->setName('get-user-info');

/*$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/

