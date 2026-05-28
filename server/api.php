<?php
date_default_timezone_set('Asia/Shanghai');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type,Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/DB.php';
require __DIR__ . '/Response.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Upload.php';
require __DIR__ . '/WxPay.php';

$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri, PHP_URL_PATH);
$basePath = '/api';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$uri = trim($uri, '/');
$parts = explode('/', $uri);
$module = $parts[0] ?? '';
$action = $parts[1] ?? '';
$param = $parts[2] ?? '';

$controllerMap = [
    'admin' => 'AdminController',
    'category' => 'CategoryController',
    'dish' => 'DishController',
    'table' => 'TableController',
    'order' => 'OrderController',
    'member' => 'MemberController',
    'coupon' => 'CouponController',
    'review' => 'ReviewController',
    'upload' => 'UploadController',
    'wx' => 'WxController',
    'setting' => 'SettingController',
    'stats' => 'StatsController'
];

if (!isset($controllerMap[$module])) {
    Response::error('接口不存在', 404);
}

$controllerFile = __DIR__ . '/controller/' . $controllerMap[$module] . '.php';
if (!file_exists($controllerFile)) {
    Response::error('控制器不存在', 404);
}

require $controllerFile;
$controllerClass = $controllerMap[$module];
$controller = new $controllerClass();

$method = $_SERVER['REQUEST_METHOD'];
$methodMap = [
    'GET' => 'get',
    'POST' => 'post',
    'PUT' => 'put',
    'DELETE' => 'delete'
];
$httpPrefix = $methodMap[$method] ?? '';

$actionMethod = $httpPrefix . str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
if (!method_exists($controller, $actionMethod)) {
    $actionMethod = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
    if (!method_exists($controller, $actionMethod)) {
        Response::error('方法不存在', 404);
    }
}

$controller->$actionMethod($param);
