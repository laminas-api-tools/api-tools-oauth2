<?php

/**
 * Bcrypt utility
 *
 * Generates the bcrypt hash value of a string
 */

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');
if (! $autoload) {
    // Attempt to locate it relative to the application root
    $autoload = realpath(__DIR__ . '/../../../autoload.php');
}

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

$bcrypt = new Laminas\Crypt\Password\Bcrypt;

if ($argc < 2) {
    printf("Usage: php bcrypt.php <password> [cost]\n");
    printf("where <password> is the user's password and [cost] is the value\nof the cost parameter of bcrypt (default is %d).\n", $bcrypt->getCost());
    exit(1);
}

if (isset($argv[2])) {
    $bcrypt->setCost($argv[2]);
}
printf ("%s\n", $bcrypt->create($argv[1]));
