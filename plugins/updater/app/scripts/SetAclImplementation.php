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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author lionel
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */


namespace app\scripts;

use OatBox\Common\ScriptRunner;
use OatBox\Common\Logger;
use app\models\UpdateService;
use app\models\KvFilePersistence;

class SetAclImplementation extends ScriptRunner {

    public function run(){
        // get list of old extensions & manifest
        $configPath = ROOT_PATH.'../generis/data/generis/config';
        $configPersistence = new KvFilePersistence($configPath, 3, true);
        
        $persistenceKey = 'tao'.'_'.'AclImplementation';
        $configPersistence->set($persistenceKey, 'taoUpdate_models_classes_accessControl_CustomAccess');
    }
}
