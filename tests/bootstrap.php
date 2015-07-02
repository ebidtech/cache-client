<?php

# Check dependencies.
$errorDependencies = 'Install dependencies to run test suite. "php composer.phar install --dev"';
$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException($errorDependencies);
}

# Add test classes to the autoloader.
$loader = require($file);
$loader->add('<TESTS_DIRECTORY>', __DIR__);
