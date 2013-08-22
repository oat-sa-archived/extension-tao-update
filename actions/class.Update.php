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
    
    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
        $this->userService = tao_models_classes_UserService::singleton();
        $this->service = taoUpdate_models_classes_Service::singleton();

   
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
	
	public function downloadRelease($releaseNumber){
	    sleep(5);
	    return json_encode(
	        array(
    	        'success' => 1,
    			'failed' => array()
		    )
	    );
	}
	
	public function extractRelease($releaseNumber){
	    sleep(10);
	    return json_encode(
	        array(
	            'success' => 1,
	            'failed' => array()
	        )
	    );
	}
	
	public function backup(){
	    sleep(2);
	    return json_encode(
	        array(
	            'success' => 1,
	            'failed' => array()
	        )
	    );
	}
	
	public function getUpdateSteps(){
        $program = array( 
            array( 
                'action' => 'downloadRelease',
                'name' => __('Download new TAO version'),
                'status' => __('stand by')
            ),
            array(
                'action' => 'extractRelease',
                'name' => __('Extract new TAO version'),
                'status' =>  __('stand by'),
            ),
            array(
                'action' => 'backup',
                'name' => __('Create Backup'),
                'status' => __('stand by'),
            ),


        );
	    echo json_encode($program);
	    
	}
	
	/**
	 * 
	 * @access
	 * @author "Lionel Lecaque, <lionel@taotesting.com>"
	 */
	private function getAvailabeUpdates(){
	    if($this->availabeUpdates == null){
	        try {
	           $this->availabeUpdates = $this->service->getAvailableUpdates();
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