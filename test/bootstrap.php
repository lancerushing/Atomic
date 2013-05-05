<?php

/**
 * Bootstrap the tests.  Set up autoLoading
 * @since 2013-03-17
 * @author Lance Rushing
 */

set_include_path(dirname(__DIR__) .
	PATH_SEPARATOR . get_include_path());

require_once 'AutoLoader.php';

$autoLoader = new \Atomic\AutoLoader(array(dirname(__DIR__)));
$autoLoader->register();

