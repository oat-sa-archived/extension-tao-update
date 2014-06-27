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

/**
 * An update scripts aiming at migrating QTI Tests. 
 * 
 * * In TAO 2.5, test definitions are stored as a single QTI-XML file located in taoQtiTests/data/testdata.
 * * Now, they are stored as directories in order to contain their auxiliary files along with the test definition. 
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class taoUpdate_scripts_update_MigrateQtiTests extends tao_scripts_Runner {

    public function run() {
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoTests');
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiTest');
        
        $ds = DIRECTORY_SEPARATOR;
        
        // 1. Find all the tests that have the QTI Test Model.
        $testClass = new core_kernel_classes_Class(TAO_TEST_CLASS);
        $qtiTests = $testClass->searchInstances(array(PROPERTY_TEST_TESTMODEL => INSTANCE_TEST_MODEL_QTI),array('recursive' => true, 'like' => false));
        
        // 2. Find the test definition file for each test.
        $testContentProp = new core_kernel_classes_Property(TEST_TESTCONTENT_PROP);
        foreach ($qtiTests as $qtiTest) {
            $testContentResource = $qtiTest->getOnePropertyValue($testContentProp);
            $testContent = new core_kernel_file_File($testContentResource);
            $qtiTestFilePath = $testContent->getAbsolutePath();
            
            $this->out("test.xml file found at '${qtiTestFilePath}'.");
            
            // 2.1. Create a directory using the current file name.
            $pathinfo = pathinfo($qtiTestFilePath);
            $targetDirPath = $pathinfo['dirname'] . $ds . $pathinfo['filename'];
            
            if (!@mkdir($targetDirPath,0777, true)) {
                $this->err("Unable to create QTI Test Content directory at location '${targetDirPath}'.");
            }
            else {
                 $this->out("QTI Test Content directory created at location '${targetDirPath}'.");   
            }
            
            // 2.2 Copy test.xml into the newly created directory.
            $qtiTestFileCopyDest = $targetDirPath . $ds . 'tao-qtitest-testdefinition.xml';
            if (!@copy($qtiTestFilePath, $qtiTestFileCopyDest)) {
                $this->err("Unable to copy test.xml file from '${qtiTestFilePath}' to '${qtiTestFileCopyDest}'.");
            }
            else {
                $this->out("test.xml file copied from '${qtiTestFilePath}' to '${qtiTestFileCopyDest}'.");
            }
            
            // 2.3 Update metadata in Knowledge Base.
            $newFileResource = $testContent->getFileSystem()->createFile('', basename($targetDirPath));
            $this->out("New File Resource in ontology refers to '" . $newFileResource->getAbsolutePath() . "'.");
            
            $qtiTest->removePropertyValues($testContentProp);
            $qtiTest->setPropertyValue($testContentProp, $newFileResource);
            
            $testContentResource->delete(true);
            
            if (!@unlink($qtiTestFilePath)) {
                $this->err("Unable to remove old test.xml file located at '${qtiTestFilePath}'.");
            }
            else {
                $this->out("Old test.xml file located at '${qtiTestFilePath}' removed from file system.");
            }
        }
        
    }
}