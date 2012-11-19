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

namespace Foomo\TestRunner;

/**
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class WorldProxy
{
	private static $outputCallback;
	//---------------------------------------------------------------------------------------------
	// ~ Variables
	//---------------------------------------------------------------------------------------------

	/**
	 * the actual test world
	 *
	 * @var stdClass
	 */
	public $world;
	/**
	 * @var \ReflectionClass
	 */
	private $refl;
	/**
	 * @var array
	 */
	private $methodTemplatesShown = array();

	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 * @param stdClass $world
	 * @param \PHPUnit_Framework_TestCase $testCase
	 */
	public function __construct($world, \PHPUnit_Framework_TestCase $testCase)
	{
		$this->world = $world;
		$this->world->testCase = $testCase;
		$this->refl = new \ReflectionClass($this->world);
	}

	//---------------------------------------------------------------------------------------------
	// ~ internal helpers
	//---------------------------------------------------------------------------------------------
	
	public static function setOutputCallBack($callback)
	{
		self::$outputCallback = $callback;
	}
	

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------

	private function showStory($storyTemplateString_____, $storyTemplateArgs____)
	{
		extract($storyTemplateArgs____);
		// the last know evil eval
		try {
			ob_start();
			eval('?>' . trim($storyTemplateString_____) . PHP_EOL);
			$buffer = ob_get_clean();
			if(substr($buffer,-1) != PHP_EOL) {
				$buffer .= PHP_EOL;
			}
			$this->handleOutput($buffer);
		} catch(\Exception $e) {
			echo 'could not run story "' . $storyTemplateString_____ . '" => ' . $e->getMessage() . PHP_EOL;
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Magic methods
	//---------------------------------------------------------------------------------------------

	/**
	 * @param string $name
	 * @return string
	 */
	public function __get($name)
	{
		if (isset($this->world->$name)) {
			return $this->world->$name;
		}
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return \Foomo\TestRunner\WorldProxy
	 */
	public function __call($name, $args)
	{

		if(method_exists($this->world, $name)) {
			$methodRefl = new \ReflectionMethod($this->world, $name);
			$docComment = $methodRefl->getDocComment();
			$methodRefl->getParameters();
			$i = 0;
			// @todo this needs to become more robust ;)
			$needle = '* @story';
			$storyFound = false;
            $lines = \explode(PHP_EOL, $docComment);
			foreach($lines as $line) {
                $line = trim($line);
				if(substr($line, 0, strlen($needle)) == $needle) {
					$argsHash = array();
					foreach($methodRefl->getParameters() as $parameterRefl) {
						/* @var $parameter \ReflectionParameter */
						if(isset($args[$i])) {
							$argsHash[$parameterRefl->getName()] = $args[$i];
						}
						$i ++;
					}
					$this->showStory(substr($line, strlen($needle)), $argsHash);
					$storyFound = true;
					break;
				}
			}
			if(!$storyFound) {
				//var_dump('no story', $name, $docComment, $lines);
			}
			ob_start();
			$ret = call_user_func_array(array($this->world,$name), $args);
			$buffer = ob_get_clean();
			$this->handleOutput($buffer);
		} else {
			// that would be nice, but it terminates test execution and that is not nice
			// $this->world->testCase->markTestIncomplete();
			if(!in_array(strtolower($name), $this->methodTemplatesShown)) {
				$this->methodTemplatesShown[] = strtolower($name);
				$output = '';
				$output .= '// missing method on your world:' . PHP_EOL;
				$output .= '/**' . PHP_EOL . '  * @story ' . $this->methodNameToStoryText($name) . PHP_EOL;
				$i = 0;
				$argsStringArray = array();
				foreach($args as $arg) {
					$output .= '  * @param ';
					switch(true) {
						case (is_scalar($arg) || is_array($arg)):
							$output .= gettype($arg);
							break;
						case (is_object($arg)):
							$output .= get_class($arg);
							break;
						default:
							$output .= 'unknown';

					}
					$output .= ' $arg_' . $i . ' comment' . PHP_EOL;
					$argsStringArray[] = '$arg_' . $i;
					$i++;
				}
				$output .= '  * @return ' . get_class($this->world) . PHP_EOL;
				$output .= '  */' . PHP_EOL;
				$output .= ' public function '.$name.'(' . implode(', ', $argsStringArray) . ')' . PHP_EOL .
                    '   {' . PHP_EOL .
					'       echo \'story step \' . __METHOD__ . \' needs to be implemented\' . PHP_EOL;' . PHP_EOL .
					'   }' . PHP_EOL;
				$this->handleOutput($output);
			}
		}
		if(isset($ret) && !is_null($ret)) {
			return $ret;
		} else {
			return $this;
		}
	}

	//---------------------------------------------------------------------------------------------
	// ~ Private methods
	//---------------------------------------------------------------------------------------------
	private function handleOutput($output)
	{
		if(isset(self::$outputCallback)) {
			call_user_func_array(self::$outputCallback, array($output));
		} else {
			echo $output;
		}
	}
	
	/**
	 * @param string $methodName
	 * @return string
	 */
	private function methodNameToStoryText($methodName)
	{
		$storyText = '';
		for($i=0;$i<strlen($methodName);$i++) {
			$current = substr($methodName, $i, 1);
			if($current != $lower = strtolower($current)) {
				$storyText .= ' ' . $lower;
			} else {
				$storyText .= $current;
			}
		}
		return $storyText;
	}
}