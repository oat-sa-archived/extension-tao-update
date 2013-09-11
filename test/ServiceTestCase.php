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
        $release = '10.10.88';       
        $releaseFile = 'TAO_10.10.88_build.zip';
        $path = $this->service->downloadRelease($release);
        $this->assertEqual($path, BASE_DATA . taoUpdate_models_classes_Service::RELEASES_DOWNLOAD_FOLDER.$releaseFile);
        $this->assertTrue(is_file($path));
        helpers_File::remove($path);
        
        $release = '10.10.77';
        $releaseFile = 'TAO_10.10.77_build.zip';
        $path = $this->service->downloadRelease($release);
        $this->assertEqual($path, BASE_DATA . taoUpdate_models_classes_Service::RELEASES_DOWNLOAD_FOLDER.$releaseFile);
        $this->assertTrue(is_file($path));
        
        helpers_File::remove($path);
        
        $release = '10.10.77';
        try {
            $path = $this->service->downloadRelease($release);
        }
        catch (Exception $e){
            $this->assertIsA($e, 'taoUpdate_models_classes_UpdateException');
        }
        
        
        
    }
    
    
    public function testBuildReleaseManifest(){
        $release = '10.10.88';       
        $folder = __DIR__ . '/tmp/';
        $result = $this->service->buildReleaseManifest($release,$folder);
        $this->assertTrue(is_file($folder . 'release.json'),'File tmp/release.json do not exist');
        $content = file_get_contents($folder . 'release.json');
        $releaseInfo = json_decode($content,true);
        $this->assertEqual($releaseInfo['version'], $release);
        $this->assertEqual(count($releaseInfo['extensions']),3);
        $extmanger = common_ext_ExtensionsManager::singleton();
        //check if actual extension are set in manifest
        $this->assertTrue(count($releaseInfo['old_extensions']) >= count($extmanger->getInstalledExtensions()) + count($extmanger->getAvailableExtensions()));
        $this->assertEqual($releaseInfo['old_root_path'],ROOT_PATH);
        helpers_File::remove($folder . 'release.json');
        $this->assertFalse(is_file($folder . 'release.json'));
    }
    
    
    public function testDelployRelease(){
        $release = '10.10.88';
        $path = $this->service->downloadRelease($release);
        $result = $this->service->deploy($release);
        $this->assertTrue(is_dir($result .'TAO_10.10.88_build'));
        helpers_File::remove($path);
    }
    

    
}