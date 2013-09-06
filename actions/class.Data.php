<?php

class taoUpdate_actions_Data extends tao_actions_CommonModule {

    protected $service ;

	public function __construct() {
		parent::__construct();
        $this->service = taoUpdate_models_classes_DataMigrationService::singleton();
		
		common_log_Dispatcher::singleton()->init(array(
		array(
		'class'			=> 'SingleFileAppender',
		'format'        => '[%s] %m',
		'threshold'		=> common_Logger::TRACE_LEVEL,
		'file'			=>  self::getTemplatePath('update.log') ,
		)));
		
		common_Logger::t('test');

	}

    
    
    protected function _isAllowed()
    {
        return true;
    }
    
    public function provideSteps(){
        echo $this->service->getUpdateScripts();
    }
    
    
    public function index(){
        $this->setData('logUrl', BASE_WWW . 'templates/update.log');
        $this->setView('logViewer.tpl');
        
    }
    
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
                $scriptName = 'taoUpdate_scripts_update_'. $script;
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
}