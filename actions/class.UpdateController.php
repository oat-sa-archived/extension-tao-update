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

class taoUpdate_actions_UpdateController extends tao_actions_CommonModule {
    
    final static $allowedRole = array();
    
    /**
     * initialize the services
     */
    public function __construct(){
        parent::__construct();
        $this->userService = tao_models_classes_UserService::singleton();
    }

	/**
	 * the say  action 
	 * @return 
	 */
	public function maintenance(){

        $this->setView('maintenance.tpl');
	}
	

	
	public function index(){
	   
	   
	   $currentUser = $this->userService->getCurrentUser(); 
	   $roles = $this->userService->getUserRoles($currentUser);
	   var_dump(array_key_exists('http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole', $roles));
	   $this->setView('index.tpl');
	}
	

}
?>