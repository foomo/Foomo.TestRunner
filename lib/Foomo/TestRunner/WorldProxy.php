<?php


namespace Foomo\TestRunner;

class WorldProxy {
	/**
	 * the actual test world
	 *
	 * @var stdClass
	 */
	public $world;
	/**
	 *
	 * @var \ReflectionClass
	 */
	private $refl;
	private $methodTemplatesShown = array();
	public function __construct($world, \PHPUnit_Framework_TestCase $testCase)
	{
		$this->world = $world;
		$this->world->testCase = $testCase;
		$this->refl = new \ReflectionClass($this->world);
	}
	private function showStory($storyTemplateString_____, $storyTemplateArgs____)
	{
		extract($storyTemplateArgs____);
		eval('?>' . $storyTemplateString_____ . PHP_EOL);
	}
	public function __get($name)
	{
		if(isset($this->world->$name)) {
			return $this->world->$name;
		}
	}
	public function __call($name, $args)
	{

		if(method_exists($this->world, $name)) {

			$methodRefl = new \ReflectionMethod($this->world, $name);
			$docComment = $methodRefl->getDocComment();
			$methodRefl->getParameters();
			$i = 0;
			// @todo this needs to become more robust ;)
			$needle = '	 * @story ';
			$storyFound = false;
			foreach(\explode(PHP_EOL, $docComment) as $line) {
				if(substr($line, 0, strlen($needle)) == $needle) {
					$argsHash = array();
					foreach($methodRefl->getParameters() as $parameterRefl) {
						/* @var $parameter \ReflectionParameter */
						$argsHash[$parameterRefl->getName()] = $args[$i];
						$i ++;
					}
					$this->showStory(substr($line, strlen($needle)), $argsHash);

					$storyFound = true;
					break;
				}
			}
			if(!$storyFound) {
				//var_dump('no story', $name, $docComment);
			}
			call_user_func_array(array($this->world,$name), $args);
		} else {
			if(!in_array(strtolower($name), $this->methodTemplatesShown)) {
				$this->methodTemplatesShown[] = strtolower($name);
				echo '	/**' . PHP_EOL . '	 * @story ' . $this->methodNameToStoryText($name) . PHP_EOL;
				$i = 0;
				$argsStringArray = array();
				foreach($args as $arg) {
					echo '	 * @param ';
					switch(true) {
						case (is_scalar($arg) || is_array($arg)):
							echo gettype($arg);
							break;
						case (is_object($arg)):
							echo get_class($arg);
							break;
						default:
							echo 'unknown';

					}
					echo ' $arg_' . $i . ' comment' . PHP_EOL;
					$argsStringArray[] = '$arg_' . $i;
					$i++;
				}
				echo '	 * @return ' . get_class($this->world) . PHP_EOL;
				echo '	 */' . PHP_EOL;
				echo '	public function '.$name.'(' . implode(', ', $argsStringArray) . ') {' . PHP_EOL . '		echo \'story step \' . __METHOD__ . \' needs to be implemented\';' . PHP_EOL . '	}' . PHP_EOL;
			}
		}
		return $this;
	}

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