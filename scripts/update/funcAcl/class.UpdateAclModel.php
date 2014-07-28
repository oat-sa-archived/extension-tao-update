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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author lionel
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */




/**
 * Cleanup script that searches for shadows of files and deletes them 
 * 
 * 
 * @author bout
 *
 */
class taoUpdate_scripts_update_funcAcl_UpdateAclModel extends tao_scripts_Runner {

    public function __construct($inputFormat = array(), $options = array()) {
        common_ext_ExtensionsManager::singleton()->getExtensionById('funcAcl');
        parent::__construct($inputFormat, $options);
    }

    public function run(){
        
        funcAcl_models_classes_Initialisation::run();
        
        $impl = new funcAcl_models_classes_FuncAcl();

        $exts = common_ext_ExtensionsManager::singleton()->getInstalledExtensions();
        foreach ($exts as $extension) {
            foreach ($extension->getManifest()->getAclTable() as $tableEntry) {
                $rule = new tao_models_classes_accessControl_AccessRule($tableEntry[0], $tableEntry[1], $tableEntry[2]);
                $impl->applyRule($rule);
            }
        }
        tao_models_classes_accessControl_AclProxy::setImplementation($impl);
    }
    
}
