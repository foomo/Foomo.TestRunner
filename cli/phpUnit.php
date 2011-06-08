<?php

// modified original phpunit

require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR .  'radactCli.inc.php');

set_error_handler(array('Foomo\\TestRunner\\Frontend\\Model', 'handleError'), E_ALL);

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

PHPUnit_TextUI_Command::main();

