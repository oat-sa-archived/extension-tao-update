<?php
use oat\generis\model\data\ModelManager;

class taoUpdate_scripts_update_SetRdfModel extends tao_scripts_Runner
{
    public function run(){
        ModelManager::setModel(new \core_kernel_persistence_smoothsql_SmoothModel(array('persistence' => 'default')));
        
    }
}

?>