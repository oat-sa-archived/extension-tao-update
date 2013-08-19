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
 * @package taoUpdate24
 * @subpackage models_classes
 *
 */

class taoUpdate24_models_classes_NotificationService extends tao_models_classes_Service{
    

    /**
     * @var string
     */
    private $releaseManifestUrl; 
    
    
    const RELEASE_FILE_PREFIX = 'TAO_';
    const RELEASE_FILE_SUFFIX = '_build.zip';
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
            || $releaseVersion['minor'] > $currentVersion['minor'] ){
                $returnValue[] = array(
                    'version' =>$version['version']
                    ,'file' => self::RELEASE_FILE_PREFIX . $version['version'] . self::RELEASE_FILE_SUFFIX
                        
                );
                continue;
            }
            if(isset($version['patchs'])){
                foreach ($version['patchs'] as $patch){
                    $patchVersion = $this->convertVersionNumber($patch['version']);
                    if($patchVersion['patch'] > $currentVersion['patch']){
                        $returnValue[] = array( 
                            'version' =>$patch['version'] 
                            ,'file' => self::RELEASE_FILE_PREFIX . $patch['version'] . self::RELEASE_FILE_SUFFIX
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
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param boolean $detailed
     * @throws taoUpdate24_models_classes_UpdateException
     * @return string
     */
    public function getVersions($detailed = false){
        
        $versionDom = @simplexml_load_file($this->getReleaseManifestUrl());
        if (!$versionDom){
            $message = __("Unable to reach the update server located at ").$this->getReleaseManifestUrl();
            throw new taoUpdate24_models_classes_UpdateException($message);
        }
        $releasesNodes = $versionDom->children();
        foreach ($releasesNodes as $releaseNode){

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
		return $this->releaseManifestUrl = $releaseManifestUrl;
	}
	
    

}