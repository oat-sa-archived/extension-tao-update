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


class taoUpdate_scripts_update_UpdateDeliveryModel extends tao_scripts_Runner {
    

    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function run() {
        
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        
        $this->migrateDeliveryToTemplate();



    }


    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    private function migrateDeliveryToTemplate(){
        $deliveryClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
        foreach ($deliveryClass->getInstances(true) as $delivery) {
            self::switchType($delivery, CLASS_DELIVERY_TEMPLATE);
        
        }
    }
    /**
     * 
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param core_kernel_classes_Resource $resource
     * @param string $newType
     */
    public static function switchType(core_kernel_classes_Resource $resource,$newType){
        
    	$resource->removePropertyValues(new core_kernel_classes_Property(RDF_TYPE));
    	$resource->setPropertyValue(new core_kernel_classes_Property(RDF_TYPE),$newType);
    }
    
    
}