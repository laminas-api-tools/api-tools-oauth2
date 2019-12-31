<?php

/**
 * Bcrypt utility
 *
 * Generates the bcrypt hash value of a string
 */

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');
$laminasEnv   = "LAMINAS_PATH";

if (file_exists($autoload)) {
    include $autoload;
} elseif (getenv($laminasEnv)) {
    include getenv($laminasEnv) . '/Laminas/Loader/AutoloaderFactory.php';
    Laminas\Loader\AutoloaderFactory::factory(array(
        'Laminas\Loader\StandardAutoloader' => array(
            'autoregister_laminas' => true
        )
    ));
}

if (!class_exists('Laminas\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load Laminas. Run `php composer.phar install` or define a LAMINAS_PATH environment variable.');
}

if ($argc < 2) {
    printf("Usage: php bcrypt.php <password> [cost]\n");
    printf("where <password> is the user's password and [cost] is the value\nof the cost parameter of bcrypt (default is 14).\n");
    exit(1);
}

$bcrypt = new Laminas\Crypt\Password\Bcrypt;
if (isset($argv[2])) {
    $bcrypt->setCost($argv[2]);
}
printf ("%s\n", $bcrypt->create($argv[1]));
