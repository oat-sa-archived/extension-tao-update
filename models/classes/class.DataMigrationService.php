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
class taoUpdate_models_classes_DataMigrationService extends tao_models_classes_Service{
    
    const UPDATE_STEP= 'updateStep.json';
    const RELEASE_INFO= 'release.json';
    
    public function getUpdateScriptsJson(){
        return @file_get_contents(BASE_DATA . self::UPDATE_STEP);
    }
    
    public function getReleaseInfo(){
        return json_decode(@file_get_contents(BASE_DATA . self::RELEASE_INFO),true);
    }
    
    
    public function installNewExtension($extlists = null){
        $extmanger = common_ext_ExtensionsManager::singleton();
        if(is_null($extlists)){
            $extlists = $extmanger->getAvailableExtensions();
        }
        
        $releaseInfo = $this->getReleaseInfo();
        $releaseExts = $releaseInfo['extensions'];

        $toInstall = array();
        foreach ($extlists as $availlableExt){

            $ext = $availlableExt->getID();
            if(in_array($ext, $releaseExts)){
                $toInstall[$ext] = $extmanger->getExtensionById($ext);
            }
        }
        while (!empty($toInstall)) {
            $modified = false;
            foreach ($toInstall as $key => $extension) {
                // if all dependencies are installed
                $installed	= array_keys(common_ext_ExtensionsManager::singleton()->getInstalledExtensions());
                $missing	= array_diff($extension->getDependencies(), $installed);
                if (count($missing) == 0) {
                    try {

                        $extinstaller = new tao_install_ExtensionInstaller($extension,false);
                        	
                        set_time_limit(60);
                        	
                        $extinstaller->install();
                    } catch (common_ext_ExtensionException $e) {
                        common_Logger::w('Exception('.$e->getMessage().') during install for extension "'.$extension->getID().'"');
                        throw new tao_install_utils_Exception("An error occured during the installation of extension '" . $extension->getID() . "'.");
                    }
                    unset($toInstall[$key]);
                    $modified = true;
                } else {
                    $missing = array_diff($missing, array_keys($toInstall));
                    foreach ($missing as $extID) {
                        $toInstall[$extID] = common_ext_ExtensionsManager::singleton()->getExtensionById($extID);
                        $modified = true;
                    }
                }
            }
            if (!$modified) {
                throw new common_exception_Error('Unfulfilable/Cyclic reference found in extensions');
            }
           
        }

        
    }
    
    public function installExtension($extension){
        // if all dependencies are installed
        $installed	= array_keys(common_ext_ExtensionsManager::singleton()->getInstalledExtensions());
        $missing	= array_diff($extension->getDependencies(), $installed);
        foreach ($missing as $dependency) {
            $this->installExtension($dependency);
        }
        
        $extinstaller = new tao_install_ExtensionInstaller($extension,false);
        set_time_limit(60);
        $extinstaller->install();
    }
    
}