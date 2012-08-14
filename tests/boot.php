<?php
define('LIBRARY_ROOT', realpath('../src'));
require_once('./resources/SplClassLoader.php');

//core lib
$loader = new \Core\SplClassLoader('ClosureForm', LIBRARY_ROOT);
$loader->register();