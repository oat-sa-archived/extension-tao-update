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
 */


class taoUpdate_scripts_update_AddDeliveryRuntimeFs extends tao_scripts_Runner {
    
    public function run() {
        
        $extension = common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        $runPath = $extension ->getConstant('BASE_PATH'). 'data' . DIRECTORY_SEPARATOR . 'compiled' . DIRECTORY_SEPARATOR;
        
        helpers_File::emptyDirectory($runPath);
        
        $runSource = tao_models_classes_FileSourceService::singleton()->addLocalSource('runtimeDirectory', $runPath);
        $provider = new tao_models_classes_fsAccess_TokenAccessProvider($runSource);
        
        taoDelivery_models_classes_RuntimeAccess::setAccessProvider($provider);
                
    }
}