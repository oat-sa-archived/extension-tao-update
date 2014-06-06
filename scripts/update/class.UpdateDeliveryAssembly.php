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


class taoUpdate_scripts_update_UpdateDeliveryAssembly extends tao_scripts_Runner {
    
    
    const OLD_COMPILED_DELIVERY = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#CompiledDelivery';
    
    const OLD_PROPERTY_COMPILEDDELIVERY_DELIVERY = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#CompiledDeliveryDelivery';
    const OLD_PROPERTY_COMPILEDDELIVERY_TIME      = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#CompiledDeliveryCompilationTime';
    const OLD_PROPERTY_COMPILEDDELIVERY_RUNTIME   = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#CompiledDeliveryRuntime';
    const OLD_PROPERTY_COMPILEDDELIVERY_DIRECTORY  = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#CompiledDeliveryCompilationFolder';
    
    
    
    public function run() {
        
        
        // load the constants
		common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');

		 $this->migrateCompiledDeliveryToAssembly();

    }
    
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    private function migrateCompiledDeliveryToAssembly(){
        $compiledDeliveryClass = new core_kernel_classes_Class(self::OLD_COMPILED_DELIVERY);
        $props = array(
            self::OLD_PROPERTY_COMPILEDDELIVERY_DELIVERY => PROPERTY_COMPILEDDELIVERY_DELIVERY,
            self::OLD_PROPERTY_COMPILEDDELIVERY_TIME => PROPERTY_COMPILEDDELIVERY_TIME,
            self::OLD_PROPERTY_COMPILEDDELIVERY_RUNTIME => PROPERTY_COMPILEDDELIVERY_RUNTIME,
            self::OLD_PROPERTY_COMPILEDDELIVERY_DIRECTORY => PROPERTY_COMPILEDDELIVERY_DIRECTORY
        );
        foreach ($compiledDeliveryClass->getInstances(true) as $compiledDelivery) {
            taoUpdate_scripts_update_UpdateDeliveryModel::switchType($compiledDelivery, TAO_DELIVERY_CLASS);
    
            $values = $compiledDelivery->getPropertiesValues(array_keys($props));
            foreach ($values as $prop =>$val){
                //compiled time was not always set in 2.5 so I set a 0 will appear as 1970 in UI
                if($prop == self::OLD_PROPERTY_COMPILEDDELIVERY_TIME && empty($val)){
    
                    if(isset($props[$prop])){
                        $compiledDelivery->setPropertyValue(new core_kernel_classes_Property($props[$prop]), '0');
                    }
    
                }
                if(!empty($val) && $prop != self::OLD_PROPERTY_COMPILEDDELIVERY_DIRECTORY){
    
                    if(isset($props[$prop])){
                        $compiledDelivery->removePropertyValues(new core_kernel_classes_Property($prop));
                        $compiledDelivery->setPropertiesValues(array($props[$prop] => $val));
                    }
                    else {
                        $this->info('could not found property ' . $prop);
                    }
                    //copy property value from templace to assembly
                    if($prop == self::OLD_PROPERTY_COMPILEDDELIVERY_DELIVERY){
                        if(isset($val[0]) && $val[0] instanceof core_kernel_classes_Resource){
                            $this->copyPropertyValuesFromTemplateToAssembly($val[0], $compiledDelivery);
                        }
                    }
                }
            }
        }
    }

    
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param core_kernel_classes_Resource $template
     * @param core_kernel_classes_Resource $assembly
     */
    private function copyPropertyValuesFromTemplateToAssembly(core_kernel_classes_Resource $template , core_kernel_classes_Resource $assembly){
         
        $resultServProps = array (
            TAO_DELIVERY_RESULTSERVER_PROP,
            TAO_DELIVERY_MAXEXEC_PROP,
            TAO_DELIVERY_START_PROP,
            TAO_DELIVERY_END_PROP
        );
    
        $templatePropsValues = $template->getPropertiesValues($resultServProps);
        $assembly->setPropertiesValues($templatePropsValues);
    
    }

    

}