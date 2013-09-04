<?php
/**
 * 
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
namespace app\scripts;

use OatBox\Common\ScriptRunner;
use app\models\UpdateService;
use OatBox\Common\Helpers\File;
use OatBox\Common\Logger;

class NewVersionDeployer extends ScriptRunner {
    
    private $releasePath = null;
    private $destination = null;


    private function getDestination(){
        if($this->destination == null){
            $service = UpdateService::getInstance();
            $releaseManifest = $service->getReleaseManifest();
            $this->destination = $releaseManifest['old_root_path'];
        }
        return $this->destination;
    }
    
    private function getReleasePath(){
        if($this->releasePath == null){
            $service = UpdateService::getInstance();
            $releaseManifest = $service->getReleaseManifest();
            $this->releasePath = DIR_DATA . $releaseManifest['release_path'];
        }
        return $this->destination;
    }

    protected function preRun() {
        if(!is_writable($this->getDestination())){
            $this->err($this->getDestination() . ' is not writable, check permission');
        }
    }
    
    public function run(){
        $releasePath = $this->getReleasePath();
        $destination = $this->getDestination();

        Logger::t('Deploy release from ' . $releasePath . ' to ' . $destination);
        
        File::copy($releasePath, $destination ,true,false);
        
    }

}