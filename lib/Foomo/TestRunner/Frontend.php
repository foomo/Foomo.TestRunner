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
 * the module app - testrunner interface
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 */
class Frontend extends \Foomo\MVC\AbstractApp implements \Foomo\Modules\ModuleAppInterface
{
	//---------------------------------------------------------------------------------------------
	// ~ Constructor
	//---------------------------------------------------------------------------------------------

	/**
	 *
	 */
	public function  __construct()
	{
		$doc = \Foomo\HTMLDocument::getInstance();
		$doc->addStylesheets(array(\Foomo\ROOT_HTTP . '/modules/' . \Foomo\TestRunner\Module::NAME . '/css/module.css'));
		$doc->addJavascripts(array(\Foomo\ROOT_HTTP . '/modules/' . \Foomo\TestRunner\Module::NAME . '/js/module.js'));
		parent::__construct(get_class($this));
	}
}