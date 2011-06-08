<?php

namespace Foomo\TestRunner;

use Foomo\MVC\AbstractApp;

/**
 * the module app - testrunner interface
 */
class Frontend extends AbstractApp implements \Foomo\Modules\ModuleAppInterface {
	public function  __construct() {
		$doc = \Foomo\HTMLDocument::getInstance();
		$doc->addStylesheets(array(\Foomo\ROOT_HTTP . '/modules/' . \Foomo\TestRunner\Module::NAME . '/css/module.css'));
		$doc->addJavascripts(array(\Foomo\ROOT_HTTP . '/modules/' . \Foomo\TestRunner\Module::NAME . '/js/module.js'));
		parent::__construct(get_class($this));
	}
}