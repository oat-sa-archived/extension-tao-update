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
 *               2015 (update and modification)  Open Assessment Technologies SA;
 */

/**
 * Platform Update controller
 *
 * @author Bertrand Chevrier  <bertrand@taotesting.com>
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @package taoUpdate
 * @subpackage actions
 */
class taoUpdate_actions_Update extends tao_actions_CommonModule {

    private $userService;
    protected $service;

    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->service = taoUpdate_models_classes_Service::singleton();
    }

    /**
     * Platform in maintenance message
     */
	public function maintenance(){
        $this->setView('maintenance.tpl');
	}

    /**
     * Render the tao update launcher
     */
    public function index(){
        $this->setData('isDesignModeEnabled', taoUpdate_helpers_Optimization::isDesignModeEnabled());
        $this->setData('installLink', ROOT_URL . taoUpdate_models_classes_Service::DEPLOY_FOLDER . 'Main/index?key=' . $this->service->getKey());
        $this->setView('index.tpl');
    }

    /**
     * Get JSON data of available updates
     */
	public function available(){
        try {
           $updates = $this->service->getAvailableUpdates();
        }
        catch (Exception $e){
            common_Logger::e('could not reach update server ' . $e->getMessage());
        }
	    return $this->returnJson($updates);
	}

	/**
	 * Run pre update steps. action and version parameters are required.
	 */
    public function run(){

	    set_time_limit(300);

        //verfiy action against a white list
        $actions = array('downloadRelease', 'deploy', 'lock');

        //required parameters
        $action  = $this->getRequestParameter('action');
        $version = $this->getRequestParameter('version');


        $response = array(
            'success' => false,
            'error'   => null
        );

        try{

            //validate parameters
            if(!in_array($action, $actions)){
                throw new common_Exception('Invalid action ' . $action);
            }
            if(empty($version)){
                throw new common_Exception('No version selected');
            }

            //run the service action
            $this->service->$action($version);

            //if no exception, we consider it successful
            $response['success'] = true;

        } catch(common_Exception $e){
            common_Logger::e($e->getMessage());
            $response['error'] = $e->getMessage();
        }

        $this->returnJson($response);
    }

	/**
	 * @see tao_actions_CommonModule::_isAllowed()
	 */
	protected function _isAllowed() {
	    if(!class_exists('tao_helpers_SysAdmin')){
	        throw new taoUpdate_models_classes_UpdateException(
	            'You cannot use update extension of version 2.5
	            for previous version of TAO, find proper update extensions'
            );
	    }

	    return tao_helpers_SysAdmin::isSysAdmin();
	}
}
?>
