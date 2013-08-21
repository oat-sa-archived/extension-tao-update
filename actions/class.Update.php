<?php
/**
 * Subjects Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoUpdate
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */

class taoUpdate_actions_Update extends tao_actions_CommonModule {
    
    private  $allowedRole ='http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole';
    private $availabeUpdates = null;
    private $userService;
    private $ReleasesService;
    
    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->ReleasesService = taoUpdate_models_classes_ReleasesService::singleton();
        $this->ReleasesService->setReleaseManifestUrl( BASE_URL . '/test/sample/releases.xml');
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
	    catch (taoUpdate_models_classes_UpdateException $e){
	        //could not reach update server
	        common_Logger::e($e->getMessage());
	    }
	    

	}
	
	public function progress(){
        $test = array( 
            "step 1" => __("lock the platform"),
            "step 2" =>  __("create backups"),
            "Step 3" => __("download last version if possible") ,
            "Step 4" => __("deploy last version") 
        );
	    echo json_encode($test);
	    
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	private function getAvailabeUpdates(){
	    if($this->availabeUpdates == null){
	        try {
	           $this->availabeUpdates = $this->ReleasesService->getAvailableUpdates();
	        }
	        catch (taoUpdate_models_classes_UpdateException $e){
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
        
        $isDesignModeEnabled = taoUpdate_helpers_Optimization::isDesignModeEnabled();
        $this->setData('isDesignModeEnabled', $isDesignModeEnabled);
        $this->setData('isUpdateAvailable', $isUpdateAvailable);
        $this->setData('hasProperRole', $hasProperRole);
        $this->setView('settings_update.tpl');
	}
	

}
?>