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


class taoUpdate_scripts_update_UpdateOntologyModel extends tao_scripts_Runner {
    
    private $updateQuery;
    private $namespaceCache;
    
    public function __construct($inputFormat = array(), $options = array())
    {
        $this->updateQuery = core_kernel_classes_DbWrapper::singleton()->prepare(
            "INSERT INTO statements VALUES (?,?,?,?,?,DEFAULT,'updateScript','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]','yyy[admin,administrators,authors]',CURRENT_TIMESTAMP)"
        );
        $this->namespaceCache = common_ext_NamespaceManager::singleton()->getAllNamespaces();
        parent::__construct($inputFormat, $options);
    }
    
    public function run(){
        $this->out('Bypassing model restriction');
        core_kernel_classes_Session::singleton()->setUpdatableModels(core_kernel_classes_Session::singleton()->getLoadedModels());
        $this->out('Loading extensions');
        $diffPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ontologyData' . DIRECTORY_SEPARATOR;
        // remove All
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
            $diffFile = $diffPath . 'diff' . ucfirst($extension->getId()) . '.php';
            if (file_exists($diffFile)) {
                $this->out('Updating model of '.$extension->getId());
                $data = include $diffFile;
                foreach ($data['toRemove'] as $tripple) {
                    $this->remove($tripple);
                }
            }
        }
        // add All
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
            $diffFile = $diffPath . 'diff' . ucfirst($extension->getId()) . '.php';
            if (file_exists($diffFile)) {
                $this->out('Updating model of '.$extension->getId());
                $data = include $diffFile;
                foreach ($data['toAdd'] as $tripple) {
                    $this->add($tripple);
                }
            }
        }
        $this->out('Restoring model restriction');
        core_kernel_classes_Session::singleton()->update();
    }
    
    private function add($data){
        $subject = new core_kernel_classes_Resource($data['s']);
        $property = new core_kernel_classes_Property($data['p']);
        $object = $data['o'];
        $lg = is_null($data['l']) ? '' : $data['l'];
        if (!$this->exists($subject, $property, $object, $lg)) {
            $nsPrefix = substr($data['s'], 0, strpos($data['s'], '#')+1);
            $ns = isset($this->namespaceCache[$nsPrefix])
                ? $this->namespaceCache[$nsPrefix]->getModelId()
                : common_ext_NamespaceManager::singleton()->getLocalNamespace()->getModelId();
            
            if (!$this->updateQuery->execute(array($ns, $subject->getUri(), $property->getUri(), $object, $lg))) {
                $this->err('Add query failed');
            }
            if (!$this->exists($subject, $property, $object, $lg)) {
                $this->err('Did not add '.$subject->getUri().':'.$property->getUri().':"'.$object.'"@'.$lg);
            }
        } else {
            $this->out('Already existed '.$subject->getUri().':'.$property->getUri().':"'.$object.'"@'.$lg);
        }
    }
    
    private function remove($data){
        $subject = new core_kernel_classes_Resource($data['s']);
        $property = new core_kernel_classes_Property($data['p']);
        $object = $data['o'];
        $lg = $data['l'];
        if ($this->exists($subject, $property, $object, $lg)) {
            if (!empty($lg)) {
                $subject->removePropertyValueByLg($property, $lg);
            } else {
                $subject->removePropertyValue($property, $object);
            }
            if ($this->exists($subject, $property, $object, $lg)) {
                $this->err('Did not remove '.$subject->getUri().':'.$property->getUri().':"'.$object.'"@'.$lg);
            }
        } else {
            $this->out('Did not exist '.$subject->getUri().':'.$property->getUri().':"'.$object.'"@'.$lg);
        }
    }
    
    private function exists($subject, $property, $object, $lg) {
        $subject = new core_kernel_classes_Resource($subject->getUri());
        $values = $subject->getPropertyValuesByLg($property, $lg);
        $found = false;
        foreach ($values as $value) {
            $raw = (string)($value instanceof core_kernel_classes_Resource ? $value->getUri() : $value);
            if ($raw == $object) {
                $found = true;
                break;
            }
        }
        return $found;
    }
}