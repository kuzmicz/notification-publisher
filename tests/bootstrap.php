<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

// https://github.com/symfony/symfony/issues/53812
ErrorHandler::register(null, false);

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

passthru(sprintf('APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup', 'test', __DIR__));
(new Symfony\Component\Filesystem\Filesystem())->remove(__DIR__.'/../var/cache/test');
