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
* @author "Lionel Lecaque, <lionel@taotesting.com>"
* @license GPLv2
* @package package_name
* @subpackage
*
*/

namespace app\models;

require ROOT_PATH.'../generis/common/persistence/interface.Driver.php';
require ROOT_PATH.'../generis/common/persistence/interface.KvDriver.php';
require ROOT_PATH.'../generis/common/class.Utils.php';
require ROOT_PATH.'../generis/common/persistence/class.PhpFileDriver.php';
require ROOT_PATH.'../generis/common/persistence/class.Persistence.php';
require ROOT_PATH.'../generis/common/persistence/class.KeyValuePersistence.php';

class KvFilePersistence extends \common_persistence_PhpFileDriver {
    
    public function __construct($baseDirectory, $levels = 3, $humanReadable = false) {
        $this->connect('tempId', array(
            'dir' => $baseDirectory,
            'levels' => $levels,
            'humanReadable' => $humanReadable
        ));
    }
    
}