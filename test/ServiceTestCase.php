<?php
/**
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

require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
require_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * @license GPLv2
 * @package taoUpdate
 * @subpackage models_classes
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 *
 */
class ServiceTestCase extends UnitTestCase {

    protected $service;
    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();
        $this->service = taoUpdate_models_classes_Service::singleton();

    }
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testCreateDeployFolder() {
        try {
            $this->service->createDeployFolder();
        }
        catch (Exception $e){
            $this->assertIsA($e, 'taoUpdate_models_classes_UpdateException');
        }
        $this->service->generateKey();
        $path = $this->service->createDeployFolder();
        $this->assertTrue(is_dir($path));
        $filepath = $path .taoUpdate_models_classes_Service::FILE_KEY;
        $this->assertTrue(is_file($filepath));
        $fileContent = @file_get_contents($filepath);
        $this->assertEqual($fileContent, $this->service->getKey());
 
    }
    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testDownloadRelease(){
        $releaseFile = 'TAO_2.4.88_build.zip';
        $path = $this->service->downloadRelease($releaseFile);
        $this->assertEqual($path, BASE_DATA . taoUpdate_models_classes_Service::RELEASES_DOWNLOAD_FOLDER.$releaseFile);
        $this->assertTrue(is_file($path));
        helpers_File::remove($path);
        
        $releaseFile = 'TAO_2.4.77_build.zip';
        $path = $this->service->downloadRelease($releaseFile);
        $this->assertEqual($path, BASE_DATA . taoUpdate_models_classes_Service::RELEASES_DOWNLOAD_FOLDER.$releaseFile);
        $this->assertTrue(is_file($path));
        
        helpers_File::remove($path);
        
        $releaseFile = 'TAO_2.4.66_build.zip';
        try {
            $path = $this->service->downloadRelease($releaseFile);
        }
        catch (Exception $e){
            $this->assertIsA($e, 'taoUpdate_models_classes_UpdateException');
        }
        
        
        
    }
    
}