<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2006-2009 (original work) Public Research Centre Henri Tudor (under the project FP6-IST-PALETTE);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
namespace OatBox\Controller;
/**
 * FlowController class
 * TODO FlowController class documentation.
 * 
 * @author J�r�me Bogaerts <jerome.bogaerts@tudor.lu> <jerome.bogaerts@gmail.com>
 */
class FlowController
{
	public function __construct()
	{

	}
	
	public function forward($moduleName, $actionName)
	{
		$context = Context::getInstance();
		
		$tempModuleName = $context->getModuleName();
		$tempActionName = $context->getActionName();
		
		$context->setModuleName($moduleName);
		$context->setActionName($actionName);
		
		$enforcer = new ActionEnforcer($context);
		$enforcer->execute();
		
		throw new \InterruptedActionException('Interrupted action after a forward',
											 $tempModuleName,
											 $tempActionName);
	}
	
	// HTTP 303 : The response to the request can be found under a different URI
	public function redirect($url, $statusCode = 302)
	{
		$context = Context::getInstance();
		
		header(HTTPToolkit::statusCodeHeader($statusCode));
		header(HTTPToolkit::locationHeader($url));
		
		throw new \InterruptedActionException('Interrupted action after a redirection', 
											 $context->getModuleName(),
											 $context->getActionName());
	}
}
?>