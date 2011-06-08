<?php
namespace Foomo\TestRunner\ErrorPrinter;
/**
 * prints errors as plain text
 */
class Plain {
	public static function renderErrors($errors)
	{
		static $idI = 0;
		$ret = '';
		foreach($errors as $error) {
			/* @var $error PHPUnit_Framework_TestFailure */
			/* @var $failed PHPUnit_Framework_Test */
			/* @var $test PHPUnit_Framework_Test */
			$failed = $error->failedTest();
			$ret .= '----------------------------------------' . PHP_EOL;
			$ret .= '  ' . $failed->getName() . PHP_EOL;
			$ret .= '----------------------------------------' . PHP_EOL;
			//var_dump($failed);
			$e = $error->thrownException();
			$ret .= '' . $e->getMessage() . '' . PHP_EOL;
			$ret .= 'in ' . $e->getFile() .', on line ' . $e->getLine() . ' saying:' . PHP_EOL ;
			$id = 'stack_' . ($idI ++);
			$ret .= '' . PHP_EOL;
			$stack = $e->getTrace();
			$stackI = count($stack);
			foreach($stack as $trace) {
				$patchProps = array('type' => 'none', 'args' => array(), 'file' => '-', 'line' => '-', 'class' => '-');
				foreach($patchProps as $prop => $dummyValue) {
					if(!isset($trace[$prop])) {
						$trace[$prop] = $dummyValue;
					}
				}
				$stackI --;
				$ret .= $stackI . ' ' . $trace['class'] . $trace['type'] . $trace['function'] . '( ';

				$args = array();
				foreach($trace['args'] as $arg) {
					$args[] = gettype($arg);
				}
				$ret .= implode(', ', $args);
				$ret .= ' )	in ' . $trace['file']	.	' on line ' . $trace['line']	. PHP_EOL;
			}
			$ret .= '';
		}
		return $ret;
	}
}