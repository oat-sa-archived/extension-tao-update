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
class ReleasesServiceTestCase extends UnitTestCase {
    /**
     * 
     * @var taoUpdate_models_classes_ReleasesService
     */
    private $service;

    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();
        $this->service = taoUpdate_models_classes_ReleasesService::singleton();
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases.xml');
    }
    
    /**
     *
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testGetVersions(){
        $version = $this->service->getVersions();
        $this->assertEqual($version['2.4']['status'], taoUpdate_models_classes_ReleasesService::RELEASE_STATUS_STABLE);
        $this->assertEqual($version['2.5']['status'], taoUpdate_models_classes_ReleasesService::RELEASE_STATUS_STABLE);
        $extensions = array ('generis','tao','filemanager','taoItems','wfEngine','taoSubjects','wfAuthoring','taoQTI','taoTests','taoDelivery','taoGroups','taoResults','ltiProvider','taoCoding','taoCampaign','ltiDeliveryProvider');
        foreach ($version['2.4']['extensions'] as $ext){
            $this->assertTrue(in_array($ext, $extensions), $ext . ' not found');
        }
        $patchs24Array = array('2.4.1','2.4.2','2.4.3','2.4.5','2.4.6','2.4.7','2.4.77','2.4.88','2.4.99');
        $patchs24 = $version['2.4']['patchs'];
        foreach (array_keys($patchs24) as $patch){
            $this->assertTrue(in_array($patch, $patchs24Array), $patch . ' not found');
            $this->assertTrue(isset($patchs24[$patch]['extensions']));
            $this->assertTrue($patchs24[$patch]['extensions'] ==$version['2.4']['extensions']);
        }

        $versionDetailed = $this->service->getVersions(true);
        $versionDetailsArray= array('2.4','2.4.1','2.4.2','2.4.3','2.4.5','2.4.6','2.4.7','2.4.77','2.4.88','2.4.99','2.5');
        foreach ($versionDetailed as $ver){
            $this->assertTrue(isset($ver['version']));
            $this->assertTrue(in_array($ver['version'], $versionDetailsArray), $ver['version'] . ' not found');
        }
        $this->assertEqual($versionDetailed['2.4']['status'] , taoUpdate_models_classes_ReleasesService::RELEASE_STATUS_STABLE);
        $this->assertEqual($versionDetailed['2.4.99']['status'] , taoUpdate_models_classes_ReleasesService::RELEASE_STATUS_PATCH);
        $this->assertEqual($versionDetailed['2.5']['status'] , taoUpdate_models_classes_ReleasesService::RELEASE_STATUS_STABLE);
        
         
    }
    
    
    
    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testDownloadRelease(){
        
        $updateSites = $this->service->getUpdateSites();
        //replace fake url with the one that will work for test case
        $updateSite = str_replace('$BASE_URL', BASE_URL, $updateSites['default']);
        $updateSites['default'] = $updateSite;
        
       
        $availableUpdates= $this->service->getAvailableUpdates();
        $releaseFile = $availableUpdates['2.4.99']['file'];
        $localFolder = dirname(__FILE__) . '/download/';

        $tmpFolder = dirname(__FILE__) .'/tmp/';
        $dlPath = $this->service->downloadRelease($releaseFile, $updateSites['default'], $localFolder);
        $this->assertTrue(is_file($dlPath));
        $zip = new ZipArchive();
        $zip->open($dlPath);
        $zip->extractTo($tmpFolder);
        $this->assertFalse($zip->locateName('version') === false);

        $this->assertTrue(is_file($tmpFolder.'version'));
        $versionFileContent = @file_get_contents($tmpFolder.'version');
        $this->assertEqual($versionFileContent, '2.4.99');
        
        try {
            $dlPath = $this->service->downloadRelease('2.4.98', $updateSites['default'], $localFolder);
        }
        catch (Exception $e){

            $this->assertIsA($e,'taoUpdate_models_classes_UpdateException');
        }
        try {
            $dlPath = $this->service->downloadRelease('2.4.99', 'http://localhost/badlnk', $localFolder);
        }
        catch (Exception $e){
        
            $this->assertIsA($e,'taoUpdate_models_classes_UpdateException');
        }
        helpers_File::remove($localFolder.$releaseFile);
        helpers_File::remove($tmpFolder.'version');

    }
    
    
    
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testGetUpdateSites(){
        
        $updateSites = $this->service->getUpdateSites();
        
        $this->assertTrue(isset($updateSites['alternate']));
        $this->assertTrue(isset($updateSites['default']));
        
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases-patchsOnly.xml');
        $updateSites = $this->service->getUpdateSites();
        //no update site in file
        $this->assertTrue(empty($updateSites));
        
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases-noNewPatch.xml');
        $updateSites = $this->service->getUpdateSites();
        // invalid update site in file
        $this->assertTrue(empty($updateSites));
    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function testGetAvailableUpdates(){
        $availableUpdates= $this->service->getAvailableUpdates();
        $current = @file_get_contents(ROOT_PATH.'version');
        $this->assertFalse(empty($availableUpdates));
        foreach ($availableUpdates as $update){
            $this->assertNotEqual($update,$current);
        }
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases-patchsOnly.xml');
        $availableUpdates= $this->service->getAvailableUpdates();
       
        if(isset($availableUpdates['2.4.99']['version'])){
            $this->assertEqual($availableUpdates['2.4.99']['version'] ,'2.4.99');
        }
        else {
            $this->fail('availableUpdates should only contain 2.4.99');
        }
        
        try {
            $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/badlink.xml');
            $availableUpdates= $this->service->getAvailableUpdates();
        }
        catch (Exception $e){
            $this->assertIsA($e,'taoUpdate_models_classes_UpdateException');
        }
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases-noNewPatch.xml');
        $availableUpdates= $this->service->getAvailableUpdates();
        $this->assertTrue(empty($availableUpdates));

    }

}