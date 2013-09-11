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
class ShieldServiceTestCase extends UnitTestCase {
    protected $service;

    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();
        $this->service = taoUpdate_models_classes_ShieldService::singleton();

    }

    public function testShield(){
        $this->assertTrue(is_file(ROOT_PATH . '/filemanager/.htaccess'));
        $this->assertTrue($this->service->shield('filemanager'));
        $this->assertTrue(is_file(ROOT_PATH . '/filemanager/htaccess.bak'));
        
        $this->assertTrue($this->service->unShield('filemanager'));
        $this->assertTrue(is_file(ROOT_PATH . '/filemanager/.htaccess'));
    
    
    }
    
    public function testShieldExtensions(){
    
        //$this->assertTrue($this->service->shieldExtensions());
        //$this->assertTrue($this->service->unShieldExtensions());
    }

    
    
}