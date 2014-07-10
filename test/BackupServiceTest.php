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

require_once dirname(__FILE__) . '/../../tao/test/TaoPhpUnitTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';
/**
 * @license GPLv2
 * @package taoUpdate
 * @subpackage models_classes
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 *
 */
class BackupServiceTest extends TaoPhpUnitTestRunner {
    /**
     * 
     * @var taoUpdate_models_classes_BackupService
     */
    private $service;
    /**
     * 
     * @var string
     */
    private $folder;
    
    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();
        $this->service = taoUpdate_models_classes_BackupService::singleton();

    }
    
    public function testCreate() {
        try {
            $this->folder = $this->service->createBackupFolder();
            $this->service->storeAllFiles($this->folder);
            $srcFile = $this->folder . DIRECTORY_SEPARATOR.
                    taoUpdate_models_classes_BackupService::SRC_BACKUP_FILE_PREFFIX. 
                    TAO_VERSION.
                    taoUpdate_models_classes_BackupService::SRC_BACKUP_FILE_SUFFIX;
            $this->assertTrue(is_file($srcFile));
            $this->service->storeDatabase($this->folder);
            $dbFile = $this->folder . DIRECTORY_SEPARATOR.
                    taoUpdate_models_classes_BackupService::DB_BACKUP_FILE_PREFFIX.
                    TAO_VERSION.
                    taoUpdate_models_classes_BackupService::DB_BACKUP_FILE_SUFFIX.
                    '.zip';
			
            $this->assertTrue(is_file($dbFile));
        }
        catch(taoUpdate_models_classes_UpdateException $e){
            $this->fail('Exception raised ' . $e->getMessage());
        }

    }
    
    public function tearDown(){
        helpers_File::remove($this->folder);
    }
    
    
}