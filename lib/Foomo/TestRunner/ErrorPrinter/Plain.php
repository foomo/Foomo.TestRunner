<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo\TestRunner\ErrorPrinter;

/**
 * prints errors as plain text
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Plain
{
	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 * @staticvar int $idI
	 * @param PHPUnit_Framework_TestFailure[] $errors
	 * @return string
	 */
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