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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author lionel
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */
use oat\tao\helpers\translation\TranslationBundle;

class taoUpdate_scripts_update_UpdateTranslationBundle extends tao_scripts_Runner {

    public function run() {
        $extensions = common_ext_ExtensionsManager::singleton()->getInstalledExtensions();
        $languages  = tao_helpers_translation_Utils::getAvailableLanguages();
        $path = ROOT_PATH . 'tao/views/locales/';

        foreach($languages as $langCode){

            try{

                $bundle = new TranslationBundle($langCode, $extensions);
                $file = $bundle->generateTo($path); 

                if($file){
                    $this->out('Success: ' . $file);
                } else {
                    $this->out('Failure generating: ' . $file);
                }

            } catch(common_excpetion_Error $e){

                $this->out('Failure: ' . $e->getMessage());
            }

        }
    }
    
}