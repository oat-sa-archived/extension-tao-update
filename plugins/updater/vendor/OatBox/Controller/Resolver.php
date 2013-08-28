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
 * This class resolve data containing into a specific URL
 *
 * @author Eric Montecalvo <eric.montecalvo@tudor.lu> <eric.mtc@gmail.com>
 */
class Resolver {

	/**
	 * @var String The Url requested
	 */
	protected $url;

	/**
	 * @var Sring The extension (extension name) requested
	 */
	protected $extension;

	/**
	 * @var Sring The module (classe name) requested
	 */
	protected $module;

	/**
	 * @var String The action (method name) requested
	 */
	protected $action;

	/**
	 * The constructor
	 * @param Request $request
	 */
    public function __construct($request) {


    	$this->module		= null;
    	$this->action		= null;
    	
		# Now resolve the Url
    	$this->resolveRequest($request);
    }

    /**
     * @return	String The module name
     */
    public function getExtensionFromURL() {
    	return $this->extension;
    }

	/**
     * @return	String The module name
     */
    public function getModule() {
    	$defaultModuleName = 'Main';
    	
    	if (defined('DEFAULT_MODULE_NAME')){
    		$defaultModuleName = DEFAULT_MODULE_NAME;
    	}
    	
    	return is_null($this->module) ? $defaultModuleName : $this->module;
    }

    /**
     * Return action name
     * @return String The action name
     */
    public function getAction() {
    	$defaultActionName = 'index';
    	
    	if (defined('DEFAULT_ACTION_NAME')){
    		$defaultActionName = DEFAULT_ACTION_NAME;
    	}
    	
    	return is_null($this->action) ? $defaultActionName : $this->action;
    }

	/**
	 * Parse the framework-object requested into the URL
	 *
	 * @param Request $pRequest 
	 */
	protected function resolveRequest($pRequest){
	    $request = $pRequest->getRequestURI();
	    
		if(empty($request)) {
			throw new ResolverException('Empty request URI in Resolver');
		}
		if (preg_match('/^\/\/+/', $request)) {
			\OatBox\Common\Logger::w('Multiple leading slashes in request URI: '.$request);
			$request = '/'.ltrim($request, '/');
		}
		$rootUrlPath	= $pRequest->getRootSubPath();
		$absPath		= parse_url($request, PHP_URL_PATH);
		if (substr($absPath, 0, strlen($rootUrlPath)) != $rootUrlPath ) {
			throw new ResolverException('Request Uri '.$request.' outside of TAO path '.ROOT_URL);
		}
		$relPath		= substr($absPath, strlen($rootUrlPath));
		$relPath		= ltrim($relPath, '/');
		$tab = explode('/', $relPath);

		if (count($tab) > 0) {
			$this->module		= isset($tab[0]) && !empty($tab[0]) ? $tab[0] : null;
			$this->action		= isset($tab[1]) && !empty($tab[1]) ? $tab[1] : null;
		} else {
			throw new ResolverException('Empty request Uri '.$request.' reached resolver');
		}
	}
}
?>
