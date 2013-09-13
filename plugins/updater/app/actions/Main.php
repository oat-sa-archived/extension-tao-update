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
namespace app\actions;

use app\models\UpdateService;
use OatBox\Common\Uri;
use OatBox\Common\Logger;
use app\scripts\OldVersionRestorer;
use app\scripts\NewVersionDeployer;

class Main extends \OatBox\Controller\Module {
    
    private $service;
    private $releaseManifest;
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function __construct(){
        $this->service = UpdateService::getInstance();
        $this->releaseManifest = $this->service->getReleaseManifest();
    }

    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $script
     */
    public function scriptRunner($script){
        $error = false;
        $errorStack = array();
        if($script != null){

            $parameters = array();
            
            $options = array(
                'argv' => array(0 => 'Script ' . $script ),
                'output_mode' => 'log_only'
            );
            try {
                $scriptName = 'app\scripts\\'. $script;
                new $scriptName(array('parameters' => $parameters),$options );
                $error = false;
            }
            catch(\Exception $e){
                
                Logger::e('Error occurs during update ' . $e->getMessage());
                $error = true;
                $errorStack[] = 'Error in script ' . $script . ' ' . $e->getMessage();
            }
            if($error){
                echo json_encode(
                    array(
                        'success' => 0,
                        'failed' => $errorStack
                    )
                );
            }
            else{
                echo json_encode(
                    array(
                        'success' => 1,
                        'failed' => array()
                    )
                );
            }

        }
        else{
            echo json_encode(
                array(
                    'success' => 0,
                    'failed' => array('not scriptname provided')
                )
            );
        }


    }
    
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function provideSteps(){
        echo $this->service->getUpdateScripts();
    }
    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function index(){
        if (!$this->hasRequestParameter('key')) {
			$this->redirect(ROOT_URL . Uri::url('maintenance','Main'));
        }
        $key = $this->getRequestParameter('key');
        if(!UpdateService::isAllowed($key)){
            $this->redirect(ROOT_URL . Uri::url('maintenance','Main'));
        }
        else{
        
            if($this->releaseManifest['status'] == 'patch'){
                $successMsg = 'Patch have been deployed, update completed, <a>return to TAO HOME</a>';
                $successLink = ROOT_URL.'..';
            }
            else {
                $successMsg = 'First phase of your update is achieved, in next phase will migrate your data according to new version. <a>Click to proceed.</a>';
                $successLink = ROOT_URL.'../taoUpdate/data/index?key=' . $this->service->getkey();
            }
            $this->setData('successLink', $successLink);
            $this->setData('successMsg', $successMsg);
            $this->setData('ROOT_URL',ROOT_URL);
            $this->setView('logViewer.tpl');
        
        }
    }

    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public function maintenance() {
        $this->setView('maintenance.tpl');;
        
    }
    
}