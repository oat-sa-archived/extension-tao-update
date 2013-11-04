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
    
    const AUTH_MODE = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringMode';
    const MODE_SIMPLE = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeSimple';
    const MODE_ADVANCED = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AuthoringModeAdvanced';
    
    const OLD_TESTRUNNER_SERVICE = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceTestContainer';
    
    const OLD_RESULT_SERVER = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ResultServer';
    const OLD_CODING_METHOD = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#DeliveryCodingMethode';
    const OLD_ACTIV = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#active';
    
    public function run() {
        
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        common_ext_ExtensionsManager::singleton()->getExtensionById('wfEngine');
        
        $deliveryClass = new core_kernel_classes_Class(TAO_DELIVERY_CLASS);
        
        $simpleModel = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAODelivery.rdf#SimpleDeliveryContent');
        $wfModel = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAODelivery.rdf#WfDeliveryContent');
        
        foreach ($deliveryClass->getInstances(true) as $delivery) {
            $values = $delivery->getPropertiesValues(array(
                self::AUTH_MODE,
                PROPERTY_DELIVERY_CONTENT,
            ));
            if (count($values[self::AUTH_MODE]) == 1 && count($values[PROPERTY_DELIVERY_CONTENT]) == 1) {
                $oldWorkflow = current($values[PROPERTY_DELIVERY_CONTENT]);
                $authoringMode = current($values[self::AUTH_MODE]); 
                $simple =  $authoringMode->getUri() == self::MODE_SIMPLE;
                $newContent = $this->createContent($oldWorkflow);
                $delivery->editPropertyValues(new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP), TAO_DELIVERY_DEFAULT_RESULT_SERVER);
                $delivery->editPropertyValues(new core_kernel_classes_Property(PROPERTY_DELIVERY_CONTENT), $newContent);
                $delivery->removePropertyValues(new core_kernel_classes_Property(self::OLD_RESULT_SERVER));
                $delivery->removePropertyValues(new core_kernel_classes_Property(self::AUTH_MODE));
                $delivery->removePropertyValues(new core_kernel_classes_Property(self::OLD_CODING_METHOD));
                $delivery->removePropertyValues(new core_kernel_classes_Property(self::OLD_ACTIV));
            } else {
                $this->err('Delivery '.$delivery->getUri().' is either already migrated or inconsistent');
            }
        }
    }
    
    private function createContent(core_kernel_classes_Resource $workflow) {
        $activities = wfEngine_models_classes_ProcessDefinitionService::singleton()->getAllActivities($workflow);
        $content = false;
        if (empty($activities)) {
            $this->out('Empty Content');
            $content = $this->createSimpleContent(null);
        }
        if (count($activities) == 1) {
            $activity = current($activities);
            $services = wfEngine_models_classes_ActivityService::singleton()->getInteractiveServices($activity);
            if (count($services) == 1) {
                $service = current($services);
                $test = $this->getTest($service);
                $this->out('Single Test Content');
                $content = $this->createSimpleContent($test);
            }
        } 
        if (empty($content)) {
            $this->out('Workflow Content');
            $content = $this->createWfContent($workflow);
        }
        return $content;
    }
    
    private function createSimpleContent($test) {
        $model = new taoSimpleDelivery_models_classes_ContentModel();
        $content = $model->createContent();
        if (!is_null($test)) {
            $saved = $content->editPropertyValues(new core_kernel_classes_Property(PROPERTY_DELIVERYCONTENT_TEST ), $test);
        }
        return $content;
    }
    
    private function createWfContent(core_kernel_classes_Resource $workflow) {
        // replace placeholders with new placeholders
        $this->requireTaoWfDelivery();
        $authoringService = wfAuthoring_models_classes_ProcessService::singleton();
        foreach (wfEngine_models_classes_ProcessDefinitionService::singleton()->getAllActivities($workflow) as $activity) {
            foreach (wfEngine_models_classes_ActivityService::singleton()->getInteractiveServices($activity) as $service) {
                $serviceDefinition = $service->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_SERVICEDEFINITION));
                if ($serviceDefinition->getUri() == self::OLD_TESTRUNNER_SERVICE) {
                    $this->out('to replace: '.$service->getLabel());
                    
                    
                    // retrieve item
                    $test = $this->getTest($service);
                    // create service
                    $newService = $this->createContainerService($test);
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
        // create delivery content
        $model = new taoWfDelivery_models_classes_WfContentModel();
        $content = $model->createContent();
        // replace workflow
        $emptyWf = $content->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_DELIVERYCONTENT_PROCESS));
        $content->editPropertyValues(new core_kernel_classes_Property(PROPERTY_DELIVERYCONTENT_PROCESS), $workflow);
        $emptyWf->delete(true);
        return $content;
    }
    
    private function getTest(core_kernel_classes_Resource $service) {
        $inParameterCollection = $service->getPropertyValuesCollection(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN));
    
        $propActualParamConstantValue = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_CONSTANTVALUE);
        $propActualParamFormalParam = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_FORMALPARAMETER);
        $propFormalParamName = new core_kernel_classes_Property(PROPERTY_FORMALPARAMETER_NAME);
    
        foreach ($inParameterCollection->getIterator() as $inParameter){
    
            $formalParameter = $inParameter->getUniquePropertyValue($propActualParamFormalParam);
            if ($formalParameter->getUri() == 'http://www.tao.lu/Ontologies/TAODelivery.rdf#FormalParamTestUri') {
                $inParameterConstant = $inParameter->getOnePropertyValue($propActualParamConstantValue);
                if (!is_null($inParameterConstant) && $inParameterConstant instanceof core_kernel_classes_Resource) {
                    return $inParameterConstant;
                } else {
                    throw new common_exception_InconsistentData('missing test constant for service '.$service->getUri());
                }
            }
        }
        throw new common_exception_InconsistentData('no test parameter for '.$service->getUri());
    }

    private function createContainerService(core_kernel_classes_Resource $test) {
        $service = new tao_models_classes_service_ServiceCall(new core_kernel_classes_Resource(INSTANCE_SERVICEDEFINITION_TESTCONTAINER));
        $service->addInParameter(new tao_models_classes_service_ConstantParameter(
            new core_kernel_classes_Resource(INSTANCE_FORMALPARAM_TESTURI),
            $test
        ));
        return $service->toOntology();
    }
    
    private function requireTaoWfDelivery() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoWfDelivery');
        if (!$ext->isInstalled()) {
            taoUpdate_models_classes_DataMigrationService::singleton()->installExtension($ext);
        }
    }
}