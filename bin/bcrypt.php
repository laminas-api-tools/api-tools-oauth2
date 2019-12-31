#!/usr/bin/env php
<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

use Laminas\Crypt\Password\Bcrypt;

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');
if (! $autoload) {
    // Attempt to locate it relative to the application root
    $autoload = realpath(__DIR__ . '/../../../autoload.php');
}

if (! $autoload) {
    throw new RuntimeException(
        'Unable to locate autoloader. Please run `composer install`.'
    );
}

include $autoload;

$help = <<< EOH
Usage:
  php bcrypt.php <password> [cost]

Arguments:
  <password>      The user's password
  [cost]          The value of the cost parameter of bcrypt.
                  (default is %d)

EOH;
$bcrypt = new Bcrypt();

if ($argc < 2) {
    printf($help, $bcrypt->getCost());
    exit(1);
}

if (isset($argv[2])) {
    $bcrypt->setCost($argv[2]);
}
printf("%s\n", $bcrypt->create($argv[1]));
