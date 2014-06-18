<?php
use oat\generis\model\data\ModelManager;

class taoUpdate_scripts_update_setRdfModel
{
    public function run(){
        ModelManager::setModel(new \core_kernel_persistence_smoothsql_SmoothModel(array('persistence' => 'default')));
        
    }
}

?>