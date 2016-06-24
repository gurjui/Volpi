<?php

// General settings:
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conect system files:
define('ROOT', dirname(__FILE__));
define('DIRSEP', DIRECTORY_SEPARATOR);

require_once ROOT.DIRSEP.'Volpi.php';

$template = new Volpi();

$template['hello'] = 'Hello!';
$template['myname'] = 'I am Dima';
$template['Volpi'] = 'my template engine Volpi!!';

$template['array'] = array('This', 'is', 'an', 'Array', '!');

$template->show('templates/index.tpl');