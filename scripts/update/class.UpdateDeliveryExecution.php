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


class taoUpdate_scripts_update_UpdateDeliveryExecution extends tao_scripts_Runner {
    
    public function run() {
        
        // load the constants

        common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        $this->abandonDeliveryExecution();
        

    }
    private function abandonDeliveryExecution(){
        $deliveryExecutionClass = new core_kernel_classes_Class(CLASS_DELVIERYEXECUTION);
        $statusProp = new core_kernel_classes_Property(PROPERTY_DELVIERYEXECUTION_STATUS);
        foreach ($deliveryExecutionClass->getInstances(true) as $execution){
            $status = $execution->getOnePropertyValue($statusProp);
            
            if($status == null || $status->getUri() == INSTANCE_DELIVERYEXEC_ACTIVE ){
                $execution->editPropertyValues($statusProp,'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryExecutionStatusAbandoned');
            }
        }
    }
    

}