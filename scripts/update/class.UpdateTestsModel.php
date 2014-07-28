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


class taoUpdate_scripts_update_UpdateTestsModel extends tao_scripts_Runner {
    
    const AUTH_MODE = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringMode';
    const MODE_SIMPLE = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeSimple';
    const MODE_ADVANCED = 'http://www.tao.lu/Ontologies/TAOTest.rdf#AuthoringModeAdvanced';
    
    const OLD_ITEMRUNNER_SERVICE = 'http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner';
    
    public function run(){
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoWfTest');
        
        $testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
        
        $simpleModelInstance = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAOTest.rdf#SimpleTestModel');
        $advancedModelInstance = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAOTest.rdf#WfTestModel');
        
        foreach ($testClass->getInstances(true) as $test) {
            $values = $test->getPropertiesValues(array(self::AUTH_MODE, TEST_TESTCONTENT_PROP));
            if (count($values[self::AUTH_MODE]) == 1 && count($values[TEST_TESTCONTENT_PROP]) == 1) {
                $oldWorkflow = current($values[TEST_TESTCONTENT_PROP]);
                $authoringMode = current($values[self::AUTH_MODE]); 
                $simple =  $authoringMode->getUri() == self::MODE_SIMPLE;
                $test->removePropertyValues(new core_kernel_classes_Property(TEST_TESTCONTENT_PROP));
                $test->removePropertyValues(new core_kernel_classes_Property(self::AUTH_MODE));
                if ($simple) {
                    taoTests_models_classes_TestsService::singleton()->setTestModel($test, $simpleModelInstance);
                } else {
                    $this->requireWfAdvTest();
                    taoTests_models_classes_TestsService::singleton()->setTestModel($test, $advancedModelInstance);
                }
                $this->replaceWfDefinition($test, $oldWorkflow);
            } else {
                $this->err('Test '.$test->getUri().' is either already migrated or inconsistent');
            }
        }
    }
    
    private function replaceWfDefinition(core_kernel_classes_Resource $test, core_kernel_classes_Resource $workflow) {
        $this->out('For test: '.$test->getLabel());
        
        $contentProp = new core_kernel_classes_Property(TEST_TESTCONTENT_PROP);
        // delete empty WF
        $emptyWF = $test->getUniquePropertyValue($contentProp);
        $test->editPropertyValues($contentProp, $workflow);
        $emptyWF->delete();
        
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
                    $newService = $this->createContainerService($item);
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
    
    private function createContainerService(core_kernel_classes_Resource $item) {
        $service = new tao_models_classes_service_ServiceCall(new core_kernel_classes_Resource(INSTANCE_ITEMCONTAINER_SERVICE));
        $service->addInParameter(new tao_models_classes_service_ConstantParameter(
            new core_kernel_classes_Resource(INSTANCE_FORMALPARAM_ITEMURI),
            $item
        ));
        return $service->toOntology();
    }
    
    private function requireWfAdvTest() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoWfAdvTest');
        if (!$ext->isInstalled()) {
            taoUpdate_models_classes_DataMigrationService::singleton()->installExtension($ext);
        }
    }
}