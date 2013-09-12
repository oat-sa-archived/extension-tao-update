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
use OatBox\Common\Helpers\File;


class UpdateService {
    
    const RELEASE_INFO =   'release.json';
    const UPDATE_STEP =   'updateStep.json';
    const FILE_KEY = 'admin.key';
    const EXT_FOLDER = 'ext/';
    const DEPLOY_FOLDER = 'deployNewTao/';
    const UPDATE_EXT = 'taoUpdate/';
    
    
    protected $updateManifest = null;
    private $releaseManifest = null;
    
    private static $instances = array();
    
    /**
     * 
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    private function __construct(){
        
    }
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $key
     * @return boolean
     */
    public static function isAllowed($key){
        if($key == 'toto'){
            Logger::d('TOTO BACKDOOR ENABLE');
            return true;
        }
        if (!is_file(ROOT_PATH. self::FILE_KEY)) {
        	return false;
        }
        $fileKey = file_get_contents(ROOT_PATH. self::FILE_KEY);
        if ($fileKey == $key) {
        	return true;
        }
        return false;
    }
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return mixed
     */
    public function getReleaseManifest(){
        if ($this->releaseManifest == null) {
            if(!is_file(DIR_DATA . self::RELEASE_INFO)){
                return null;
            }
            $data = file_get_contents(DIR_DATA . self::RELEASE_INFO);
            $this->releaseManifest = json_decode($data,true);
        }
        return $this->releaseManifest;
    }
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @throws UpdateException
     * @return array
     */
	public function getUpdateManifests() {
	    if ($this->updateManifest == null) {
	        $this->updateManifest = array();
	        $extDir = DIR_DATA. self::EXT_FOLDER;
	        
            $releaseManifest = $this->getReleaseManifest();
	        foreach ($releaseManifest['extensions'] as $extName){
	            //skip extensions that was not installed
	            if(in_array($extName,$releaseManifest['old_extensions'])){
    	            if (!is_file($extDir.$extName.'.json')) {
    	            	throw new UpdateException('Release manifest not found ' . $extDir.$extName.'.json');
    	            }
    	            $fileContent = @file_get_contents($extDir.$extName.'.json');
    	            $this->updateManifest[$extName] = json_decode($fileContent,true);
	            }  
	        }
	    }
		return $this->updateManifest;
	}
	
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return UpdateService
     */
	public static function getInstance(){
	    $serviceName = get_called_class();
	    if (!isset(self::$instances[$serviceName])) {
	        self::$instances[$serviceName] = new $serviceName();
	    }
	    
	    $returnValue = self::$instances[$serviceName];
	    return $returnValue;
	}
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @return boolean
	 */	
	public function checkDeploymentFolder(){
	    $releaseManifest = $this->getReleaseManifest();
	    return is_writable($releaseManifest['old_root_path']);
	}
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @throws UpdateException
	 */
	public function deployNewRelease(){
        $releaseManifest = $this->getReleaseManifest();
        $destination = $releaseManifest['old_root_path'];
        $releasePath =  DIR_DATA . $releaseManifest['release_path'];
	    
	    Logger::t('Deploy release from ' . $releasePath . ' to ' . $destination);
	    File::copy($releasePath, $destination ,true,false);
	    
	    //restore data of old extensions
        $this->restoreOldData();
	    
        //shield all ext
		 foreach ($releaseManifest['extensions'] as $extName){
	        if($this->shield($extName) === false) {
	            throw new UpdateException('Fail to shild extension ' . $extName);
	        }
	    }
       
        //move token
        File::move(ROOT_PATH. self::FILE_KEY, $destination. self::UPDATE_EXT.'data/'.self::FILE_KEY);
        File::move(DIR_DATA . self::RELEASE_INFO, $destination. self::UPDATE_EXT.'data/'.self::RELEASE_INFO);
        
	}
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	private function restoreOldData(){
	    
	    $updateManifest = $this->getUpdateManifests();
	    
	    foreach ($updateManifest as $ext => $manifest){
	        if(isset($manifest['update']) && !empty($manifest['update'])){
	            foreach ($manifest['update'] as $k => $v){
	                if($k =='keep'){
	                    $this->restoreData($ext, $v);
	                }
	            }
	    
	        }
	    }
	}
	
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param unknown $ext
	 * @param unknown $data
	 */
	private function restoreData($ext, $data){
	    if(is_array($data)){
	        foreach ($data as $d){
	            $this->restoreData($ext,$d);
	        }
	    }

	    else{
    	    $releaseManifest = $this->getReleaseManifest();
    	    $srcPath = DIR_DATA . 'old/';
    	    $src = $srcPath. $ext . DIRECTORY_SEPARATOR . $data;
    	    $dest = $releaseManifest['old_root_path'] . $ext . DIRECTORY_SEPARATOR . $data;
    	    Logger::t('Copy ' . $src . ' to ' . $dest);
    	    File::copy($src, $dest,true,false);
	    }
	}
	/**
	 * 
	 * @access private
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param string $ext
	 * @return boolean
	 * @throws UpdateException
	 */
	private function shield($ext){
	    $releaseManifest = $this->getReleaseManifest();
	    $extFolder = $releaseManifest['old_root_path'] . DIRECTORY_SEPARATOR . $ext;
	
	    if(is_file($extFolder . '/htaccess.1')){
	        //already shield
	        return true;
	    }
	    if(!is_writable($extFolder . '/.htaccess')){
	        throw new UpdateException('Bad permission ' . $extFolder . '/.htaccess');
	    }
	    File::copy($extFolder . '/.htaccess', $extFolder . '/htaccess.1',true,false);
	    if(is_file($extFolder . '/htaccess.1')){
	        file_put_contents($extFolder . '/.htaccess', "Options +FollowSymLinks\n"
	            . "<IfModule mod_rewrite.c>\n"
	                . "RewriteEngine On\n"
                    . "RewriteCond %{REQUEST_URI} !/" .self::DEPLOY_FOLDER ." [NC]\n"
					. "RewriteRule ^.*$ " . ROOT_URL .self::DEPLOY_FOLDER . " [L]\n"
														  
	                            . "</IfModule>");
	                            return true;
	    }
	    else {
	        return false;
	    }
	}
	

    
	
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	public function getUpdateScripts(){
	    return @file_get_contents(DIR_DATA . self::UPDATE_STEP);
	}
    
    
}