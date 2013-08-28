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

namespace app\scripts;

use OatBox\Common\ScriptRunner;
use app\models\UpdateService;
use OatBox\Common\Helpers\File;
use OatBox\Common\Logger;

class OldVersionArchiver extends ScriptRunner {
    
    

	protected function preRun() {

	    
	    $service = UpdateService::getInstance();
	    $extManifests = $service->getUpdateManifests();
	    Logger::t('Checking Right on old before archiving' );
	    foreach ($extManifests as $ext =>$update){
	        if(!is_writable(OLD_ROOT_PATH . $ext)){
	            self::err('Extensions ' . $ext . ' do not exist or folder is not writable check priviledge',true  );
	        }
	        $this->out(  $ext . ' OK' );
	    }
	    Logger::t('Checking Right on old installation destination '  );
	    if(!is_writable(ROOT_PATH.DIR_DATA .'old/')){
	        self::err('Folder ' .ROOT_PATH. DIR_DATA .'old/' . ' do not exist or folder is not writable check priviledge',true  );
	    }
	    Logger::t('Precheck OK' );
	    
	}

    
    public function run(){
         $service = UpdateService::getInstance();
        $extManifests = $service->getUpdateManifests();
        
        
        foreach ($extManifests as $ext =>$update){
            Logger::t('Moving Old'. $ext . ' from ' .OLD_ROOT_PATH . ' to ' . ROOT_PATH.DIR_DATA.'old/'.$ext);
            //File::move(OLD_ROOT_PATH . $ext, DIR_DATA .'old'.$ext);
            
        }
        //$this->out('Moving Old Filemanger from ' .OLD_ROOT_PATH . ' to ' . ROOT_PATH.DIR_DATA.'old/');
        //File::move(OLD_ROOT_PATH . 'filemanager' , ROOT_PATH.DIR_DATA .'old/' . 'filemanager');
      

    }
}