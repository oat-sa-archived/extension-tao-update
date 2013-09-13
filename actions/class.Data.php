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
        if (!$this->hasRequestParameter('key')) {
            $this->setView('maintenance.tpl');
        }
        $key = $this->getRequestParameter('key');
        $fileKey =  DIR_DATA . taoUpdate_models_classes_Service::FILE_KEY; 
        if(is_file($fileKey) && @file_get_content($fileKey) == $key) {
            $session = new taoUpdate_models_classes_Session();
            common_session_SessionManager::startSession($session);
            return true;
        }
        return false;
    }
    
    public function provideSteps(){
        echo $this->service->getUpdateScriptsJson();
    }
    
    
    public function index(){
        $this->setData('logUrl', BASE_WWW . 'templates/update.log');
        $this->setView('logViewer.tpl');
        
    }
    
    public function scriptRunner($script,$extension){
        $error = false;
        $errorStack = array();
        if(isset($extension) && $extension != null){
            $ext = common_ext_ExtensionsManager::singleton()->getExtensionById($extension);
        }
        if($script != null){
    
            $parameters = array();
    
            $options = array(
                'argv' => array(0 => 'Script ' . $script ),
                'output_mode' => 'log_only'
            );
            try {
                $scriptName =  $script;
                if(!class_exists($scriptName)){
                    throw new taoUpdate_models_classes_UpdateException('Could not find scriptName class ' . $script);
                }
                new $scriptName(array('parameters' => $parameters),$options );
                  
                $error = false;
            }
            catch(Exception $e){
    
                common_Logger::e('Error occurs during update ' . $e->getMessage());
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