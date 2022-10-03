<?php

use App\controller\HomeController;
use App\infrastructure\router\Router;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

define('DEBUG_TIME', microtime(true));
define('VIEW_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);

if (defined('DEBUG_TIME')) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
}

(new Router())
    ->get('/', new HomeController(), 'home')
    ->run();
