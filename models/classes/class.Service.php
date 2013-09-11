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
 * @package taoUpdate
 * @subpackage models_classes
 *
 */
class taoUpdate_models_classes_Service extends tao_models_classes_Service{
    
    const RELEASES_LOCAL_FOLDER = 'local/';
    const RELEASES_DOWNLOAD_FOLDER = 'download/';
    
    const DEPLOY_FOLDER = 'deployNewTao/';
    const FILE_KEY = 'admin.key';
    const RELEASE_FOLDER = 'release/';
    const RELEASE_INFO =   'release.json';
    
    const UPDATOR_CONFIG = 'boot/config.json';  
    const UPDATOR_SRC =   'plugins/updater/';
    
   
    
    private $key;
    
    private $releasesService;
    private $backupService;




	protected function __construct() {
	    $this->releasesService = taoUpdate_models_classes_ReleasesService::singleton();
	    $this->releasesService->setReleaseManifestUrl(RELEASES_MANIFEST);
	    $this->backupService = taoUpdate_models_classes_BackupService::singleton();
	}

    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function generateKey(){
        $this->key = base_convert(time(),10,5);
    }
    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @throws taoUpdate_models_classes_UpdateException
     * @return string
     */
    public function createDeployFolder(){
        if($this->getKey() == null){
            throw  new taoUpdate_models_classes_UpdateException('key is missing, should have been generated before' );
        }
        $path = ROOT_PATH . self::DEPLOY_FOLDER;
        if(is_dir($path)){
             common_Logger::i('Folder already exist remove it ' . $path);
            helpers_File::remove($path);
        }
        if(!mkdir($path, 0755, true)) {
            throw  new taoUpdate_models_classes_UpdateException('fail to create deploy folder' );
        }
        
        file_put_contents($path . self::FILE_KEY, $this->key);

        return $path;
     }

     /**
      * 
      * @access
      * @author "Lionel Lecaque, <lionel@taotesting.com>"
      * @return string
      */
	public function getKey() {
		return $this->key;
	}
	

	
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
	public function getAvailableUpdates(){
	   return $this->releasesService->getAvailableUpdates();
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param unknown $release
	 * @param unknown $deployFolder
	 */
	public function buildReleaseManifest($release,$deployFolder){
	    
	    $data = $this->releasesService->getReleaseManifest($release);
	    $data['old_root_path'] = ROOT_PATH;
	    $data['release_path'] =  self::RELEASE_FOLDER . $this->releasesService->getReleaseFolder($release);
	    $extmanger = common_ext_ExtensionsManager::singleton();
	    $avExt = $extmanger->getAvailableExtensions();
	    
	    //build array with id of availlabe
	    array_walk($avExt, function(&$ext){
	    	   $ext = $ext->getID();
	    });
	    
	    $instExt =  array_keys($extmanger->getInstalledExtensions());
	    $ext = array_merge($avExt,$instExt);
	    $data['old_extensions'] = $ext;
	    $releaseManifest = json_encode($data);
	    file_put_contents($deployFolder . self::RELEASE_INFO, $releaseManifest);;
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param unknown $array
	 */
	public function setUpdaterConstant($array){
	    $data = json_encode($array);
	    file_put_contents(BASE_PATH . self::UPDATOR_SRC . self::UPDATOR_CONFIG, $data);
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @return mixed
	 */
	public function getUpdaterConstant(){
	    $json = file_get_contents(BASE_PATH . self::UPDATOR_SRC .  self::UPDATOR_CONFIG);
	    return  json_decode($json,true);
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param unknown $folder
	 */
	public function deployUpdater($folder){
	    helpers_File::copy(BASE_PATH . self::UPDATOR_SRC, $folder, true,false);
	}
	
	/**
	 * 
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param string $release
	 * @throws taoUpdate_models_classes_UpdateException
	 */
	public function deploy($release){
	    $this->generateKey();
	    $releaseFileName = $this->releasesService->getReleaseFileName($release);
	    $downloadFile = BASE_DATA . self::RELEASES_DOWNLOAD_FOLDER . $releaseFileName;
	    if(is_file($downloadFile)){
	        $updaterConstants = $this->getUpdaterConstant();	        
	        $updaterDataFolder = $updaterConstants['constants']['DIR_DATA'];
	        
	        //update constants in adapter from local installation
	        $updaterConstants['constants']['ROOT_URL'] = ROOT_URL . self::DEPLOY_FOLDER;
	        $this->setUpdaterConstant($updaterConstants);
	        
	        $deployFolder = $this->createDeployFolder();
	        $this->deployUpdater($deployFolder);
	        $this->buildReleaseManifest($release,$deployFolder . $updaterDataFolder);
	        return $this->releasesService->extractRelease($downloadFile, $deployFolder . $updaterDataFolder . self::RELEASE_FOLDER);
	    }
	    else {
	        throw new taoUpdate_models_classes_UpdateException('Fail to extract Release, file missing ' . $downloadFile);
	    }
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	public function backup(){
	   
	    $folder = $this->backupService->createBackupFolder();
	    $this->backupService->storeAllFiles($folder);
	    $this->backupService->storeDatabase($folder);
	}
	
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $release
     * @throws taoUpdate_models_classes_UpdateException
     * @return string
     */
	public function downloadRelease($release){
	    
	    $fileName = $this->releasesService->getReleaseFileName($release);

	    $updateSites = $this->releasesService->getUpdateSites();
	    
	    $downloadFolder = BASE_DATA . self::RELEASES_DOWNLOAD_FOLDER ;
        try {
            $path = $this->releasesService->downloadRelease($fileName, $updateSites['default'], $downloadFolder);
            
            
        } catch (taoUpdate_models_classes_ReleaseDownloadException $e) {
            common_Logger::i('Main update Site not reachable try to connect alternate');
            try {
            $path = $this->releasesService->downloadRelease($fileName, $updateSites['default'], $downloadFolder);
            
        	
            } catch (taoUpdate_models_classes_ReleaseDownloadException $e2) {
                common_Logger::i('Problem getting release from distant server will use local file instead');
                $srcFolder = BASE_DATA . self::RELEASES_LOCAL_FOLDER ;
                if (is_file($srcFolder.$fileName)) {
                        helpers_File::copy($srcFolder.$fileName, $downloadFolder . $fileName,false);
                        $path = $downloadFolder . $fileName;
                }
                else{
                    throw new taoUpdate_models_classes_UpdateException('Could not find a release with file ' . $fileName);
                }

            }
        }
	    return $path;

	   
	    
	}

}