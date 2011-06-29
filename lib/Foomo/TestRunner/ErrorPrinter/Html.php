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
 * renders errors in html format
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Html
{
	//---------------------------------------------------------------------------------------------
	// ~ Static variables
	//---------------------------------------------------------------------------------------------

	/**
	 * @var array
	 */
	private static $patchTraceProps = array(
		'type' => 'none',
		'args' => array(),
		'file' => '-',
		'line' => '-',
		'class' => '-'
	);

	//---------------------------------------------------------------------------------------------
	// ~ Public static methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param array $errors
	 * @return string
	 */
	public static function renderErrors($errors)
	{
		$ret = '';
		foreach($errors as $error) {
			$failedTest = $error->failedTest();
			/* @var $failedTest PHPUnit_Framework_TestCase */
			// var_dump($errors, $failedTest);return;

			$stack = array();

			$fullTrace = $error->thrownException()->getTrace();
			foreach(\array_slice($fullTrace, 2) as $traceEntry) {
				if(isset($traceEntry['args']) && is_array($traceEntry['args'])) {
					\array_walk($traceEntry['args'], function(&$arg) {
						switch(true) {
							case \is_resource($arg):
								$arg = 'resource';
								break;
							case is_array($arg):
								$arg = 'array';
								break;
							case \is_null($arg):
								$arg = 'null';
								break;
							case \is_object($arg):
								$arg = \get_class($arg);
								break;
							case \is_bool($arg):
								$arg?'true':'false';
								break;
							default:
								$arg = (string) $arg;
						}
						if(strlen($arg) > 97) {
							$arg = substr($arg, 0, 97) . '...';
						}
					});
				}
				foreach(self::$patchTraceProps as $prop => $dummyValue) {
					if(!isset($traceEntry[$prop])) {
						$traceEntry[$prop] = $dummyValue;
					}
				}

				$stack[] = (object) array(
					'file' => $traceEntry['file'],
					'line' => $traceEntry['line'],
					'call' => \call_user_func_array(
						function($traceEntry) {
							if($traceEntry['class'] != '-') {
								return  $traceEntry['class'] . $traceEntry['type'] . $traceEntry['function'];
							} else {
								return $traceEntry['function'];
							}
						},
						array($traceEntry)
					),
					'args' => $traceEntry['args']
				);
			}
			$model = (object) array(
				'name' => get_class($error->failedTest()) . '::' . $failedTest->getName(),
				'message' => $failedTest->getStatusMessage(),
				'stack' => \array_reverse($stack)

			);
			$ret .= \Foomo\TestRunner\Module::getView(__CLASS__, 'html', $model)->render();
		}
		return $ret;
	}
}