<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$commands = [
    ['bin/console', 'doctrine:database:drop', '--force', '--env=test'],
    ['bin/console', 'doctrine:database:create', '--env=test'],
    ['bin/console', 'doctrine:migrations:migrate', '--no-interaction', '--env=test'],
    ['bin/console', 'doctrine:fixtures:load', '--no-interaction', '--env=test'],
];

foreach ($commands as $command) {
    $process = new Process($command, dirname(__DIR__), ['APP_ENV' => 'test']);
    $process->mustRun();
}