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
class ZipTest extends UnitTestCase {


    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();

    }

    public function testCompressFile(){
        $src = dirname(__FILE__).'/sample/releases.xml';
        $dest = dirname(__FILE__).'/backup/releases.xml.zip';
        taoUpdate_helpers_Zip::compressFile($src,$dest);
        $zip = new ZipArchive();
        $zip->open($dest);
        $this->assertFalse($zip->locateName('releases.xml') === false);
        helpers_File::remove($dest);
    }
    
    public function testCompressFolder() {
        $files = array (
            'releases-noNewPatch.xml',
            'releases-patchsOnly.xml',
            'releases.xml',
            'folder/',
            'folder/emptyFile',
            'emptyFolder/',
        );
        $dest = dirname(__FILE__).'/backup/test.zip';
        $src = dirname(__FILE__).'/sample';
        taoUpdate_helpers_Zip::compressFolder($src,$dest);
        $this->assertTrue(is_file($dest));
        $zip = new ZipArchive();      
        $zip->open($dest);
        foreach ($files as $file){
            $this->assertFalse($zip->locateName($file) ===false,$file . ' not found');
        }
        $this->assertFalse($zip->locateName('.svn'));
        helpers_File::remove($dest);
        $dest = dirname(__FILE__).'/backup/test2.zip';
        taoUpdate_helpers_Zip::compressFolder($src,$dest,true);
        $files = array (
            'sample/releases-noNewPatch.xml',
            'sample/releases-patchsOnly.xml',
            'sample/releases.xml',
            'sample/folder/',
            'sample/folder/emptyFile',
            'sample/emptyFolder/',
        );
        $this->assertTrue(is_file($dest));
        $zip = new ZipArchive();
        $zip->open($dest);
        for( $i = 0; $i < $zip->numFiles; $i++ ){
            $stat = $zip->statIndex( $i );
            //cehck no .svn added in zip
            $this->assertFalse(strpos($stat['name'], '.svn') > 0);
        
        }
        foreach ($files as $file){
            $this->assertFalse($zip->locateName($file) === false);
        }
        helpers_File::remove($dest);

    }
    
    
}