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
 * Copyright (c) $2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package taoUpdate
 * @subpackage models_classes
 *
 */

class taoUpdate_models_classes_ReleasesService extends tao_models_classes_Service{
    

    /**
     * @var string
     */
    private $releaseManifestUrl; 
    
    /**
     * @var SimpleXMLElement
     */
    private $dom = null;
    
   

    const RELEASE_FILE_PREFIX = 'TAO_';
    const RELEASE_FILE_SUFFIX = '_build';
    const RELEASE_FILE_EXT = '.zip';
    const RELEASE_STATUS_STABLE = 'stable';
    const RELEASE_STATUS_PATCH = 'patch';
    
    /**
     * 
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param SimpleXMLElement $releaseNode
     * @return array
     */
    private function getReleaseInfo($releaseNode){

        $versionNode = $releaseNode->xpath('version');
        $commentNode = $releaseNode->xpath('comment');
        
        $patchs = $releaseNode->xpath('patchs');

        $returnValue = array (
            'version'	=> (string) $versionNode[0]
            , 'comment'	=> (string) trim($commentNode[0])
        );
        if (!empty($patchs)){
        	foreach ($patchs[0] as $patch){
        	   $patchNode = $patch->xpath('version');
        	   $patchNodeValue = (string) $patchNode[0];
	           $returnValue['patchs'][$patchNodeValue] = $this->getReleaseInfo($patch);
        	}
        }
        $extensions = $releaseNode->xpath('extensions');
        if (!empty($extensions)){
            foreach ($extensions[0] as $extension){
                $returnValue['extensions'][] = (string) $extension;
            }
        }
        return $returnValue;
    }
    

    /**
     *
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $file
     */
    public function extractRelease($file,$dest){
        return taoUpdate_helpers_Zip::extractFile($file,$dest);
    
    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $releaseName
     * @return string
     */
    public function getReleaseFolder($releaseName){
        return self::RELEASE_FILE_PREFIX . $releaseName . self::RELEASE_FILE_SUFFIX;
    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $releaseName
     * @return string
     */
    public function getReleaseFileName($releaseName){
        return $this->getReleaseFolder($releaseName). self::RELEASE_FILE_EXT;
    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $releaseName
     * @param string $updateSite
     * @param string $localFolder
     * @throws taoUpdate_models_classes_UpdateException
     * @return string
     */
    public function downloadRelease($releaseFileName,$updateSite, $localFolder){
        //file is only 18M but default php value is 128M but some packaging (ie MAMP) are set to 8MB
        ini_set('memory_limit', '128');
        $curl = curl_init();
        $distantRelease = $updateSite . $releaseFileName;
        if(!$fp = @fopen($localFolder.$releaseFileName, 'w')){
            throw new taoUpdate_models_classes_UpdateException('Fail to open stream check permission on ' . $localFolder.$releaseFileName);
        }    
        curl_setopt ($curl, CURLOPT_URL, $distantRelease);
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl,  CURLOPT_RETURNTRANSFER, TRUE);
        $contents = curl_exec ($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $httpCode == 200 ) {     
            fwrite($fp, $contents);
            
        } else {
            if(is_file($localFolder.$releaseFileName)){
                helpers_File::remove($localFolder.$releaseFileName);
            }
            throw new taoUpdate_models_classes_ReleaseDownloadException($httpCode, $releaseFileName, $updateSite);      
        }
        return $localFolder.$releaseFileName;
       
    }
    
    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array
     */
    public function getAvailableUpdates(){
        $returnValue = array();
        $versions = $this->getVersions();
        $currentVersion = $this->getCurrentVersion();

        foreach ($versions as $version){
        
            $releaseVersion = $this->convertVersionNumber($version['version']);
            if($releaseVersion['major'] > $currentVersion['major']
            || ($releaseVersion['minor'] > $currentVersion['minor'] 
                && $releaseVersion['major'] == $currentVersion['major'])){
                $returnValue[$version['version']] = array(
                    'version' =>$version['version']
                    ,'file' => $this->getReleaseFileName($version['version'])
                        
                );
                continue;
            }
            //handle patchs
            if(isset($version['patchs'])){
                foreach ($version['patchs'] as $patch){
                    $patchVersion = $this->convertVersionNumber($patch['version']);
                    if($patchVersion['patch'] > $currentVersion['patch'] && 
                    $releaseVersion['major'] == $currentVersion['major'] &&
                    $releaseVersion['minor'] == $currentVersion['minor']){
                        $returnValue[$patch['version'] ] = array( 
                            'version' =>$patch['version'] 
                            ,'file' => $this->getReleaseFileName($patch['version'])
                        );
                    }
                }
            }
        }
        return $returnValue;
    }
    
    /**
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $versionNumber
     * @return array
     */
    private function convertVersionNumber($versionNumber){
        
        $major = substr($versionNumber, 0, strpos($versionNumber, '.'));        
        $tmp = substr($versionNumber, strpos($versionNumber, '.')+1);       
        $minor =  strpos($tmp, '.') ? substr($tmp, 0, strpos($tmp, '.')) : $tmp;
        $minor = str_replace('-alpha', '', $minor);
        $minor = str_replace('-beta', '', $minor);
        $patch = strpos($tmp, '.') ? substr($tmp, strpos($tmp, '.')+1) : '0';
        return array(
            'full' => $versionNumber
            ,'major' => $major
            ,'minor' => $minor
            ,'patch' => $patch
        );
    }
    
    /**
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array
     */
    private function getCurrentVersion(){
        $versionFileContent = @file_get_contents(ROOT_PATH.'version');
        return $this->convertVersionNumber($versionFileContent);        
    }
    
    
    
    
    /**
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @throws taoUpdate_models_classes_UpdateException
     * @return SimpleXMLElement
     */
    private function getDom(){
        if ($this->dom == null) {
            $this->dom = @simplexml_load_file($this->getReleaseManifestUrl());
            if (!$this->dom){
                $message = "Unable to reach the update server located at " .$this->getReleaseManifestUrl();
                common_Logger::w($message);
                common_Logger::i('Use local file instead');
                $this->dom =  @simplexml_load_file(RELEASES_LOCAL_MANIFEST);
            }
        }
        return $this->dom;
    }
 

    public function getReleaseManifest($release){
        $versions = $this->getVersions(true);
        return $versions[$release];
    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param boolean $detailed
     * @throws taoUpdate_models_classes_UpdateException
     * @return string
     */
    public function getVersions($detailed = false){

        $releasesNodes = $this->getDom()->xpath('releases');
        foreach ($releasesNodes[0] as $releaseNode){            
            $version = $this->getReleaseInfo($releaseNode);
            if ($detailed && isset($version['patchs'])) {
            	foreach ($version['patchs'] as $patch){
            	    $returnValue[$patch['version']] = $patch;
            	    $returnValue[$patch['version']]['status'] = self::RELEASE_STATUS_PATCH;
            	}
            	
            	$returnValue[$version['version']] = array(
            	    'version'    => $version['version'],
            	    'comment'    => $version['comment'],
            	    'extensions' => $version['extensions'],
            	    'status'       => self::RELEASE_STATUS_STABLE,
            	);
            }
            else {
                $returnValue[$version['version']] = $version;
                $returnValue[$version['version']]['status'] = self::RELEASE_STATUS_STABLE;
            }
        }
        return $returnValue;
    }

    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $id
     * @param string $url
     * @param string $key
     * @return boolean
     */
    private function validateUpdateSite($id,$url,$key){
        //dummy check need to be improved
        if (md5($id.$url )== $key) {
        	return true;
        }
        common_Logger::i('Fail to validate server with id ' . $id );
        return false;
    }
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array 
     */
    public function getUpdateSites(){
        $returnValue = array();
        $updateSitesNodes = $this->getDom()->xpath('updateSites');
        foreach ($updateSitesNodes[0] as $updateSiteNode){
            $idNode = $updateSiteNode->xpath('id');
            $urlNode = $updateSiteNode->xpath('url');
            $keyNode = $updateSiteNode->xpath('key');
            $id = (string) $idNode[0];
            $url = (string) $urlNode[0];
            $key = (string)$keyNode[0];
            if($this->validateUpdateSite($id,$url,$key)){
                if(UPDATE_SITE_IS_LOCAL){
                    common_Logger::d('replace url with a local one for update site');
                    $url = str_replace('$BASE_URL', BASE_URL, $url);
                    $returnValue [$id] = $url;
                }
                else {
                    $returnValue [$id] = $url;
                }
            }     
        }

        return $returnValue;
    }
    
	/**
	 * @access private
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @return string
	 */
	private function getReleaseManifestUrl() {
		return $this->releaseManifestUrl;
	}
	
	/**
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @param string $releaseManifestUrl
	 */
	public function setReleaseManifestUrl($releaseManifestUrl) {
	    //reset dom
	    $this->dom = null;
		return $this->releaseManifestUrl = $releaseManifestUrl;
	}
	
    

}