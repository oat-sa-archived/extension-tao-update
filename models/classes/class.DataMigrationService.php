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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package taoUpdate
 * @subpackage models_classes
 *
 */
class taoUpdate_models_classes_DataMigrationService extends tao_models_classes_Service{
    
    const UPDATE_STEP= 'updateStep.json';
    const RELEASE_INFO= 'release.json';
    
    public function getUpdateScriptsJson(){
        return @file_get_contents(BASE_DATA . self::UPDATE_STEP);
    }
    
    public function getReleaseInfo(){
        return json_decode(@file_get_contents(BASE_DATA . self::RELEASE_INFO),true);
    }
    
    
}