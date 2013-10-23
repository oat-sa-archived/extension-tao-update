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
    
    public function run(){
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoTests');
        
        $testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
        
        $simpleModelInstance = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAOTest.rdf#SimpleTestModel');
        $advancedModelInstance = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAOTest.rdf#WfTestModel');
        
        foreach ($testClass->getInstances() as $test) {
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
    
    private function replaceWfDefinition($test, $workflow) {
        $emptyWF = $test->getUniquePropertyValue(new core_kernel_classes_Property(TEST_TESTCONTENT_PROP));
        // delete empty WF
        // foreach activity in workflow
        // foreach service
        // if service is item, replace service definition and parameters
    }
    
    private function requireWfAdvTest() {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoWfAdvTest');
        if (!$ext->isInstalled()) {
            taoUpdate_models_classes_DataMigrationService::singleton()->installExtension($ext);
        }
    }
}