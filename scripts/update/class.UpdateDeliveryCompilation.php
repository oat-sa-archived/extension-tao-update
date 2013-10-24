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


class taoUpdate_scripts_update_UpdateDeliveryCompilation extends tao_scripts_Runner {
    
    const OLD_DELIVERY_COMPILED = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryProcess';
    const OLD_DELIVERY_ISCOMPILED = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#Compiled';
    
    public function run() {
        
        common_log_Dispatcher::singleton()->init(array(
            array(
            'class'            => 'UDPAppender',
            'host'            => '127.0.0.1',
            'port'            => 5775,
            'threshold'        => 1
            )
        ));
        
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        common_ext_ExtensionsManager::singleton()->getExtensionById('wfEngine');        
        
        $deliveryClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
        
        foreach ($deliveryClass->getInstances() as $delivery) {
            $this->out('looking for compiled '.$delivery->getLabel());
            $workflow = $delivery->getOnePropertyValue(new core_kernel_classes_Property(self::OLD_DELIVERY_COMPILED));
            if (!is_null($workflow) && $workflow instanceof core_kernel_classes_Resource) {
                $this->out('Compilation found for '.$delivery->getLabel());
                $compiled = $this->buildCompiledDelivery($delivery, $workflow);
                $delivery->editPropertyValues(new core_kernel_classes_Property(PROPERTY_DELIVERY_ACTIVE_COMPILATION), $compiled);
            } else {
                $this->out('No compilation found for '.$delivery->getLabel());
            }
            $delivery->removePropertyValues(new core_kernel_classes_Property(self::OLD_DELIVERY_COMPILED));
            $delivery->removePropertyValues(new core_kernel_classes_Property(self::OLD_DELIVERY_ISCOMPILED));            
        }
    }
    
    private function buildCompiledDelivery(core_kernel_classes_Resource $delivery, core_kernel_classes_Resource $workflow) {
        $directory = $this->getCompilationDirectory($delivery);
        $this->replaceItemRunner($workflow, $directory);
        
        $serviceCall = new tao_models_classes_service_ServiceCall(new core_kernel_classes_Resource(INSTANCE_SERVICE_PROCESSRUNNER));
        $param = new tao_models_classes_service_ConstantParameter(
            new core_kernel_classes_Resource(INSTANCE_FORMALPARAM_PROCESSDEFINITION),
            $workflow
        );
        $serviceCall->addInParameter($param);
        
        $compilationClass = new core_kernel_classes_Class(CLASS_COMPILEDDELIVERY);
        $compilationInstance = $compilationClass->createInstanceWithProperties(array(
            RDFS_LABEL                         => $delivery->getLabel(),
            PROPERTY_COMPILEDDELIVERY_DELIVERY => $delivery,
            PROPERTY_COMPILEDDELIVERY_FOLDER   => $directory,
            PROPERTY_COMPILEDDELIVERY_TIME     => time(),
            PROPERTY_COMPILEDDELIVERY_RUNTIME  => $serviceCall->toOntology()
        ));
        return $compilationInstance;
    }
    
    protected function getCompilationDirectory( core_kernel_classes_Resource $delivery)
    {
        $returnValue = (string) '';
    
        $fs = taoDelivery_models_classes_RuntimeAccess::getFileSystem();
        $basePath = $fs->getPath();
        $relPath = substr($delivery->getUri(), strpos($delivery->getUri(), '#') + 1).DIRECTORY_SEPARATOR;
        $absPath = $fs->getPath().$relPath;
    
        if (! is_dir($absPath)) {
            if (! mkdir($absPath)) {
                throw new taoDelivery_models_classes_CompilationFailedException('Could not create delivery directory \'' . $absPath . '\'');
            }
        }
    
        return $fs->createFile('', $relPath);
    }
    
    private function replaceItemRunner(core_kernel_classes_Resource $workflow, $directory) {
        
    }
    
    private function compileItem(core_kernel_classes_Resource $workflow) {
    
    }
}