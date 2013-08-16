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
 * Copyright (c) ${year} (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
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
    
    /**
     * 
     * @var SimpleXMLElement
     */
    private $versionDom;
    
    private $releaseFilePreffix = 'TAO_';
    private $releaseFileSuffix = '_build.zip';
    
    /**
     * 
     * @access
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
	           $returnValue['patchs'][] = $this->getReleaseInfo($patch);
        	}
        }
        return $returnValue;
    }
    
    
    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array
     */
    public function getAvaillableUpdates(){
        $returnValue = array();
        $versions = $this->getVersions();
        $currentVersion = $this->getCurrentVersion();
         
        foreach ($versions as $version){
        
            $releaseVersion = $this->convertVersionNumber($version['version']);
            if($releaseVersion['major'] > $currentVersion['major']
            || $releaseVersion['minor'] > $currentVersion['minor'] ){
                $returnValue[$version['version']] = 
                        $this->releaseFilePreffix . 
                        $version['version'] . 
                        $this->releaseFileSuffix;
                continue;
            }
            if(isset($version['patchs'])){
                foreach ($version['patchs'] as $patch){
                    $patchVersion = $this->convertVersionNumber($patch['version']);
                    if($patchVersion['patch'] > $currentVersion['patch']){
                        $returnValue[$patch['version']] = 
                                $this->releaseFilePreffix . 
                                $patch['version'] . 
                                $this->releaseFileSuffix;
                    }
                }
            }
        }
        return $returnValue;
    }
    
    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $versionNumber
     * @return array
     */
    public function convertVersionNumber($versionNumber){
        
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
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array
     */
    public function getCurrentVersion(){
        $versionFileContent = @file_get_contents(ROOT_PATH.'version');
        return $this->convertVersionNumber($versionFileContent);        
    }
    
    
    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return array
     */
    public function getVersions(){
        
        $this->versionDom = @simplexml_load_file($this->getReleaseManifestUrl());
        if (!$this->versionDom){
            $message = __("Unable to reach the update server located at ").$releasesManifestUrl;
            echo $message;
        }
        $releasesNodes = $this->versionDom->children();
        foreach ($releasesNodes as $releaseNode){
            $returnValue[] = $this->getReleaseInfo($releaseNode);
        }
        return $returnValue;
    }

	/**
	 * @access public
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 * @return string
	 */
	public function getReleaseManifestUrl() {
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