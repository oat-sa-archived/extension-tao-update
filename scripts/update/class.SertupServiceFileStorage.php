<?php

class taoUpdate_scripts_update_SertupServiceFileStorage extends tao_scripts_Runner
{
    public function run() {

        
        $publicDataPath = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR;
        $privateDataPath = FILES_PATH.'tao'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR;
        
        if (file_exists($publicDataPath)) {
            helpers_File::emptyDirectory($publicDataPath);
        }
        if (file_exists($privateDataPath)) {
            helpers_File::emptyDirectory($privateDataPath);
        }
        
        $publicFs = tao_models_classes_FileSourceService::singleton()->addLocalSource('public service storage', $publicDataPath);
        $privateFs = tao_models_classes_FileSourceService::singleton()->addLocalSource('private service storage', $privateDataPath);
        
        $provider = tao_models_classes_fsAccess_TokenAccessProvider::spawnProvider($publicFs);
        
        tao_models_classes_service_FileStorage::configure($privateFs, $publicFs, $provider);
    }
}

?>
