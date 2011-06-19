<?php

// modified original phpunit
set_error_handler(array('Foomo\\TestRunner\\Frontend\\Model', 'handleError'), E_ALL);

require_once 'PHP/CodeCoverage/Filter.php';
PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

if (extension_loaded('xdebug')) {
	xdebug_disable();
}

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

PHPUnit_TextUI_Command::main();