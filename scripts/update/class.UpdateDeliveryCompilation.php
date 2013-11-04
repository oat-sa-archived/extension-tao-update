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
    
    const OLD_ITEMRUNNER_SERVICE = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner';
    
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
        
        foreach ($deliveryClass->getInstances(true) as $delivery) {
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
        $authoringService = wfAuthoring_models_classes_ProcessService::singleton();
        // foreach activity in workflow
        foreach (wfEngine_models_classes_ProcessDefinitionService::singleton()->getAllActivities($workflow) as $activity) {
            foreach (wfEngine_models_classes_ActivityService::singleton()->getInteractiveServices($activity) as $service) {
                $serviceDefinition = $service->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_SERVICEDEFINITION));
                if ($serviceDefinition->getUri() == self::OLD_ITEMRUNNER_SERVICE) {
                    $this->out('to replace: '.$service->getLabel());
        
        
                    // retrieve item
                    $item = $this->getItem($service);
                    // create service
                    $itemDirectory = $this->createNamedSubDirectory($directory, $activity);
                    $newService = $this->compileItem($item, $itemDirectory);
                    $newService->setLabel($service->getLabel());
        
                    $activity->removePropertyValue(new core_kernel_classes_Property(PROPERTY_ACTIVITIES_INTERACTIVESERVICES), $service);
                    //delete its related properties
                    $deleted = $authoringService->deleteActualParameters($service);
                    //delete call of service itself
                    $deleted = $service->delete(true);
        
                    $activity->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACTIVITIES_INTERACTIVESERVICES), $newService);
                }
            }
            $this->out('done activity: '.$activity->getLabel());
        }
    }

    private function getItem(core_kernel_classes_Resource $service) {
        $inParameterCollection = $service->getPropertyValuesCollection(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN));
    
        $propActualParamConstantValue = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_CONSTANTVALUE);
        $propActualParamFormalParam = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_FORMALPARAMETER);
        $propFormalParamName = new core_kernel_classes_Property(PROPERTY_FORMALPARAMETER_NAME);
    
        foreach ($inParameterCollection->getIterator() as $inParameter){
    
            $formalParameter = $inParameter->getUniquePropertyValue($propActualParamFormalParam);
            if ($formalParameter->getUri() == 'http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamItemUri') {
                $inParameterConstant = $inParameter->getOnePropertyValue($propActualParamConstantValue);
                if (!is_null($inParameterConstant) && $inParameterConstant instanceof core_kernel_classes_Resource) {
                    return $inParameterConstant;
                } else {
                    throw new common_exception_InconsistentData('missing item constant for service '.$service->getUri());
                }
            }
        }
        throw new common_exception_InconsistentData('no item parameter for '.$service->getUri());
    }
    
    protected function createNamedSubDirectory(core_kernel_file_File $rootDirectory, $activity) {
        $name = md5($activity->getUri());
        $relPath = $rootDirectory->getRelativePath() . DIRECTORY_SEPARATOR . $name;
        $absPath = $rootDirectory->getAbsolutePath() . DIRECTORY_SEPARATOR . $name;
    
        if (!is_dir($absPath) && !mkdir($absPath)) {
            throw new taoItems_models_classes_CompilationFailedException("Could not create sub-directory '${absPath}'.");
        }
        
        return $rootDirectory->getFileSystem()->createFile('', $relPath);
    }
    
    private function compileItem(core_kernel_classes_Resource $item, $directory) {
        $compiler = taoItems_models_classes_ItemsService::singleton()->getCompiler($item);
        $service = $compiler->compile($directory);
        return $service->toOntology();
    }
}