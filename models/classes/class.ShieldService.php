<?php
/**
 *
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
 *
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * 
 */
class taoUpdate_models_classes_ShieldService extends tao_models_classes_Service{

    
    /**
     *
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @return boolean
     */
    public function shieldExtensions(){
        $extmanger = common_ext_ExtensionsManager::singleton();
        $extlists = $extmanger->getInstalledExtensions();
        $returnvalue = true;
        try {
        foreach (array_keys($extlists) as $ext){
            $returnvalue &= $this->shield($ext,taoUpdate_models_classes_Service::DEPLOY_FOLDER);
        }
        }
        catch (taoUpdate_models_classes_UpdateException $e){
            common_Logger::e('Error during shield, revert all extensions');
            foreach (array_keys($extlists) as $ext){
                $this->unShield($ext);
            }
            throw $e;
        }
        return $returnvalue;
    }
    
    /**
     *
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $ext
     * @return boolean
     */
    public function shield($ext , $destination){
        $extFolder = ROOT_PATH . DIRECTORY_SEPARATOR . $ext;
        if(is_file($extFolder . '/htaccess.1')){
            throw new taoUpdate_models_classes_UpdateException('Previous lock, htaccess.1 still exits, delete it in ' . $extFolder);
        }
        helpers_File::copy($extFolder . '/.htaccess', $extFolder . '/htaccess.1',true,false);
        if(is_file($extFolder . '/htaccess.1') && is_writable($extFolder . '/.htaccess')){
            file_put_contents($extFolder . '/.htaccess', "Options +FollowSymLinks\n"
                . "<IfModule mod_rewrite.c>\n"
                    . "RewriteEngine On\n"
                    . "RewriteCond %{REQUEST_URI} !/views/  [NC]\n"
                    
//                        . "RewriteCond %{REQUEST_URI} !/" .$destination ." [NC]\n"
                    . "RewriteRule ^.*$ " . ROOT_URL .$destination . " [L]\n"
                . "</IfModule>");
            return true;
        }
        else {
            throw new taoUpdate_models_classes_UpdateException('.htaccess is not writtable in ' . $extFolder);
        }
    }
    
    /**
     *
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param unknown $ext
     * @return boolean
     */
    public function unShield($ext){
        $extFolder = ROOT_PATH . DIRECTORY_SEPARATOR . $ext;
         if(!is_file($extFolder.'/htaccess.1')){
             common_Logger::d('Previous lock, htaccess.1 do not exits something may have go wrong, please check');
             return false;
         }
        if(unlink($extFolder.'/.htaccess')){
            return tao_helpers_File::move($extFolder.'/htaccess.1', $extFolder.'/.htaccess',true,false);
        }
        else {
            common_Logger::i('Fail to remove htaccess in ' . $ext . ' . You may copy by hand file htaccess.1');
            return false;
        }
    }
}