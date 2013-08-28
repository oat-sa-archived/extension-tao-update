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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */
namespace app\models;

use OatBox\Common\Logger;

class UpdateService {
    
    protected $updateManifest = null;
    private static $instances = array();
    
    private function __construct(){
        
    }
    
    public static function isAllowed($key){
        if($key == 'toto'){
            Logger::d('TOTO BACKDOOR ENABLE');
            //return true;
        }
        $fileKey = file_get_contents(ROOT_PATH. 'admin.key');
        if ($fileKey == $key) {
        	return true;
        }
        return false;
    }

	public function getUpdateManifests() {
	    if ($this->updateManifest == null) {
	        $this->updateManifest = array();
	        $extDir = DIR_DATA.'/ext/';
	        $dh  = opendir($extDir);
	        
	        while (false !== ($filename = readdir($dh))) {
	            if(strpos($filename, '.json') !== false){
	                $extName = substr($filename,0,strpos($filename, '.json'));
	                $fileContent = file_get_contents($extDir.$filename);
	                $this->updateManifest[$extName] = json_decode($fileContent,true);
	            }
	        }
	    }
		return $this->updateManifest;
	}
	

	public static function getInstance(){
	    $serviceName = get_called_class();
	    if (!isset(self::$instances[$serviceName])) {
	        self::$instances[$serviceName] = new $serviceName();
	    }
	    
	    $returnValue = self::$instances[$serviceName];
	    return $returnValue;
	}
    
    
    
    
}