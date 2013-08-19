<?php
/**
 * Subjects Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoMigration
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */

class taoUpdate24_actions_Update extends tao_actions_CommonModule {
    
    private  $allowedRole ='http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
    private $availabeUpdates = null;
    private $userService;
    private $notificationService;
    
    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->notificationService = taoUpdate24_models_classes_NotificationService::singleton();
        $this->notificationService->setReleaseManifestUrl( BASE_URL . '/test/sample/releases.xml');
    }

	/**
	 * the say  action 
	 * @return 
	 */
	public function maintenance(){

        $this->setView('maintenance.tpl');
	}
	
    /**
     * 
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
	public function availableUpdates(){
	    try {
    	    echo json_encode($this->getavailabeUpdates());
	    }
	    catch (taoUpdate24_models_classes_UpdateException $e){
	        //could not reach update server
	        common_Logger::e($e->getMessage());
	    }
	    

	}
	
	
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	private function getAvailabeUpdates(){
	    if($this->availabeUpdates == null){
	        try {
	           $this->availabeUpdates = $this->notificationService->getAvailableUpdates();
	        }
	        catch (taoUpdate24_models_classes_UpdateException $e){
	            //could not reach update server
	            common_Logger::e($e->getMessage());
	        }
	    }
	    return $this->availabeUpdates;
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	public function settings(){
        $currentUser = $this->userService->getCurrentUser();
        $roles = $this->userService->getUserRoles($currentUser);
        $hasProperRole = array_key_exists($this->allowedRole, $roles);
        $isUpdateAvailable = $this->getAvailabeUpdates() != null ? true : false;
        
        $isDesignModeEnabled = taoUpdate24_helpers_Optimization::isDesignModeEnabled();
        $this->setData('isDesignModeEnabled', $isDesignModeEnabled);
        $this->setData('isUpdateAvailable', $isUpdateAvailable);
        $this->setData('hasProperRole', $hasProperRole);
        $this->setView('settings_update.tpl');
	}
	

}
?>