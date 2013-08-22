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
    
    const DEPLOY_FOLDER = 'deployNewTAO/';
    const FILE_KEY = 'admin.key';
    
    private $key = null;
    
    private $releasesService;





	protected function __construct() {
	    $this->releasesService = taoUpdate_models_classes_ReleasesService::singleton();
	    $this->releasesService->setReleaseManifestUrl(RELEASES_MANIFEST);

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
        if($this->key == null){
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
     * @param unknown $fileName
     * @throws taoUpdate_models_classes_UpdateException
     * @return string
     */
	public function downloadRelease($fileName){
	    

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