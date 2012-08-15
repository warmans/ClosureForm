<?php
define('DS', DIRECTORY_SEPARATOR);
define('LIBRARY_ROOT', __DIR__.DS.'..'.DS.'src');
require_once(__DIR__.DS.'resources'.DS.'SplClassLoader.php');

//core lib
$loader = new \Core\SplClassLoader('ClosureForm', LIBRARY_ROOT);
$loader->register();