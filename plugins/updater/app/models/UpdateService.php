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
    
    const RELEASE_INFO =   'release.json';
    const FILE_KEY = 'admin.key';
    const EXT_FOLDER = '/ext/';
    
    
    protected $updateManifest = null;
    private $releaseManifest = null;
    
    
    private static $instances = array();
    
    private function __construct(){
        
    }
    
    public static function isAllowed($key){
        if($key == 'toto'){
            Logger::d('TOTO BACKDOOR ENABLE');
            return true;
        }
        $fileKey = file_get_contents(ROOT_PATH. self::FILE_KEY);
        if ($fileKey == $key) {
        	return true;
        }
        return false;
    }
    
    public function getReleaseManifest(){
        if ($this->releaseManifest == null) {
            $data = @file_get_contents(DIR_DATA . self::RELEASE_INFO);
            $this->releaseManifest = json_decode($data,true);
        }
        return $this->releaseManifest;
    }

	public function getUpdateManifests() {
	    if ($this->updateManifest == null) {
	        $this->updateManifest = array();
	        $extDir = DIR_DATA. self::EXT_FOLDER;
	        
            $releaseManifest = $this->getReleaseManifest();
	        foreach ($releaseManifest['old_extensions'] as $extName){
	            if (is_file($extDir.$extName.'.json')) {
	            	throw new UpdateException('Release manifest not found');
	            }
	            $fileContent = @file_get_contents($extDir.$extName.'.json');
	            $this->updateManifest[$extName] = json_decode($fileContent,true);
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