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
    protected  $service;
    
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
	 * @param unknown $action
	 * @param unknown $versionName
	 */
	public function run($action,$versionName){
	    $error = false;
	    $failed = array();
	    
	    if($versionName == null || $action == null){
	        $error = true;
	        $failed[] = 'No version selected';
	    }
	    else {
	        try {    	  
    	       $this->service->$action($versionName);
    	    }
    	    catch (taoUpdate_models_classes_UpdateException $e){
    	        $error = true;
    	        $failed[] = $e->getMessage();
    	    }
	    }
	    
	    if(!$error){
	        echo json_encode(
	            array(
	                'success' => 1,
	                'failed' => array()
	            )
	        );
	    }
	    else {
	        echo json_encode(
	            array(
	                'success' => 0,
	                'failed' => $failed
	            )
	        );
	    }
	}
	
	public function getUpdateSteps(){
        $program = array( 
            array( 
                'action' => 'downloadRelease',
                'name' => __('Download new TAO version'),
                'status' => __('stand by')
            ),
            array(
                'action' => 'deploy',
                'name' => __('Extract new TAO version'),
                'status' =>  __('stand by'),
            ),
            array(
                'action' => 'backup',
                'name' => __('Create Backup and lock TAO'),
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
	        catch (Exception $e){
	            common_Logger::e('could not reach update server ' . $e->getMessage());
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
	    
	    try {
        $currentUser = $this->userService->getCurrentUser();
        $roles = $this->userService->getUserRoles($currentUser);
        $hasProperRole = array_key_exists($this->allowedRole, $roles);
        $isUpdateAvailable = $this->getAvailabeUpdates() != null ? true : false;

        $this->setData('updatesaAvailable', json_encode($this->getAvailabeUpdates()));
        $this->setData('hasProperRole', $hasProperRole);
        
        $isDesignModeEnabled = taoUpdate_helpers_Optimization::isDesignModeEnabled();
        $this->setData('isDesignModeEnabled', $isDesignModeEnabled);
        $this->setData('isUpdateAvailable', $isUpdateAvailable);
        $this->setData('hasProperRole', $hasProperRole);
        $successMsg =  __('New Version have been downloaded and will now be extracted, we will now replace current installation. ');
        $successUrl = ROOT_URL .taoUpdate_models_classes_Service::DEPLOY_FOLDER . 'Main/index?key=' . $this->service->getKey();  
        
        
        $this->setData('successMsg', $successMsg);
        $this->setData('successUrl', $successUrl);
        $this->setView('settings_update.tpl');
	    }
	    catch (Exception $e){
	         //
	    }
	}
	

}
?>