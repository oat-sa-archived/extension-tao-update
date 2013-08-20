<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) ${year} (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package taoUpdate
 * @subpackage models_classes
 *
 */
class taoUpdate_models_classes_BackupService extends tao_models_classes_Service{

    const BACKUP_DIR = 'backup';

    const SRC_BACKUP_FILE_PREFFIX ='TAO_full_backup_';
    const SRC_BACKUP_FILE_SUFFIX ='-data-src.zip';
    
    const DB_BACKUP_FILE_PREFFIX = 'TAO_DB_';
    const DB_BACKUP_FILE_SUFFIX = '.sql';
    
    public function createBackupFolder(){
        $timestamps = date('Ymd-His', time());
        $basePath = BASE_DATA . self::BACKUP_DIR . DIRECTORY_SEPARATOR ;
        
        $path = $basePath . $timestamps  ;
        
        if ( !mkdir($path, 0755, true)) {
            throw  new taoUpdate_models_classes_UpdateException('fail to createdir folder');
        }
        
        return $path;
        
    }

    public function storeAllFiles($folder)
    {
        $filepath = $folder . DIRECTORY_SEPARATOR.self::SRC_BACKUP_FILE_PREFFIX. TAO_VERSION.self::SRC_BACKUP_FILE_SUFFIX;
        taoUpdate_helpers_Zip::compressFolder(ROOT_PATH, $filepath);
    
    }
    
    public function storeDatabase($folder){
        $dbBackupHelper = new taoUpdate_helpers_DbBackup();
        $fileContent = $dbBackupHelper->backup();
        $filepath = $folder . DIRECTORY_SEPARATOR.self::DB_BACKUP_FILE_PREFFIX. TAO_VERSION.self::DB_BACKUP_FILE_SUFFIX;
        if(!file_put_contents($filepath, $fileContent)){
            throw  new taoUpdate_models_classes_UpdateException('fail to create SQL file');
        }
        if(is_file($filepath)){
            taoUpdate_helpers_Zip::compressFile($filepath, $filepath.'.zip');
            if(is_file($filepath.'.zip')){
                helpers_File::remove($filepath);
            }
        }

    }
    

    
}