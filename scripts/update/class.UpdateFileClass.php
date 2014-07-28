<?php
/**
 * 
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
 *
 */


class taoUpdate_scripts_update_UpdateFileClass extends tao_scripts_Runner {
    
    const OLD_CLASS = 'http://www.tao.lu/Ontologies/generis.rdf#VersionedFile';
    
    const OLD_PROPERTY_PATH = 'http://www.tao.lu/Ontologies/generis.rdf#VersionedFilePath';
    const OLD_PROPERTY_FILESYSTEM = 'http://www.tao.lu/Ontologies/generis.rdf#VersionedFileRepository';
    
    public function run(){
        $oldClass = new core_kernel_classes_Class(self::OLD_CLASS);
        $newClass = new core_kernel_classes_Class(CLASS_GENERIS_FILE);
        
        $oldPath = new core_kernel_classes_Property(self::OLD_PROPERTY_PATH);
        $oldFS = new core_kernel_classes_Property(self::OLD_PROPERTY_FILESYSTEM);
        
        foreach ($oldClass->getInstances() as $file) {
            $file->setType($newClass);
            $values = $file->getPropertiesValues(array($oldPath, $oldFS));
            if (count($values[self::OLD_PROPERTY_FILESYSTEM]) == 1 && count($values[self::OLD_PROPERTY_PATH]) == 1) {
                $file->setPropertiesValues(array(
                    PROPERTY_FILE_FILEPATH => current($values[self::OLD_PROPERTY_PATH]),
                    PROPERTY_FILE_FILESYSTEM => current($values[self::OLD_PROPERTY_FILESYSTEM])
                ));
                $file->removePropertyValues($oldPath);
                $file->removePropertyValues($oldFS);
            } else {
                common_Logger::w('Resource '.$file->getUri().' is either already migrated or inconsistent');
            }
        }
    }
}