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
 * Copyright (c) ${year} (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
require_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 * @license GPLv2
 * @package taoUpdate24
 * @subpackage models_classes
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 *
 */
class NotificationServiceTestCase extends UnitTestCase {
    private $service;

    /**
     * tests initialization
     */
    public function setUp(){
        TaoTestRunner::initTest();
        $this->service = taoUpdate24_models_classes_NotificationService::singleton();
        $this->service->setReleaseManifestUrl( BASE_URL . '/test/sample/releases.xml');
    }
    
    public function testMethod(){
       
       var_dump($this->service->getAvaillableUpdates());

        $this->fail('not imp yet');
    }
}