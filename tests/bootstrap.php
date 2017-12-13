<?php

error_reporting(E_ALL | E_STRICT);

//	include the composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';

//	autoload abstract TestCase classes in test directory
$loader->addPsr4( 'dekuan\\deuclientpro\\', __DIR__ . '/../src' );
$loader->addPsr4( 'dekuan\\deuclientpro\\Models\\', __DIR__ . '/../src/Models' );
$loader->addPsr4( 'dekuan\\delib\\', __DIR__ . '/../vendor/dekuan/delib/src/' );

