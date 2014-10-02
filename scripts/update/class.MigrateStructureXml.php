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

/**
 * An update scripts for migrating old structures.xml
 */
class taoUpdate_scripts_update_MigrateStructuresXml extends tao_scripts_Runner {

    private $_doc;
    private $_actions;
    private $_oldActions = array();
    private $_newActions = array();

    /**
     * travels recursively throughout nodes
     *
     * @access private
     * @param  domnode $main
     * @return void
     */
    private function exploreNode(DOMNode $main) {
        foreach ($main->childNodes as $node) {
            $method =  false;
            switch($node->nodeName) {
            case 'structure':
            case 'tree':
            case 'action':
                $method = $node->nodeName . 'NodeUpdate';
                break;
            case 'actions':
                $this->_actions = $node;
            }
            if ($method !== false) {
                $this->$method($node);
            }
            if ($node->childNodes->length > 1) {
                $this->exploreNode($node);
            }
        }
    }

    /**
     * updates the STRUCTURE node
     *
     * @access private
     * @param  domnode $node
     * @return void
     */
    private function structureNodeUpdate(DOMNode &$node) {
        /*
         * Add/remove attributes
         */
        $node->removeAttribute('visible');
        $node->setAttribute('group', 'main');

        /*
         * Appends icon element
         */
        $element = $this->_doc
                        ->createElement('icon');
        $structureId = $node->getAttribute('id');
        if (!empty($structureId)) {
            $element->setAttribute('id', 'icon-' . strtolower($structureId));
        }
        $node->appendChild($element);
    }

    /**
     * updates the TREE node
     *
     * @access private
     * @param  domnode $node
     * @return void
     */
    private function treeNodeUpdate(DOMNode &$node) {
        // List of new attributes
        $newAttributes = array(
            'selectClass' => 'edit_class',
            'selectInstance' => 'edit_item',
            'moveInstance' => 'move',
            'delete' => 'delete'
        );

        $len = $node->attributes->length;
        if ($len) {
            for ($i = $len - 1; $i >= 0; --$i) {
                $attr = $node->attributes->item($i);
                $name = $attr->nodeName;
                switch ($name) {
                    case 'name':
                    case 'className':
                    case 'dataUrl':
                        // these are still valid attributes
                        break;
                    default:
                        if (isset($newAttributes[$name])) {
                            unset($newAttributes[$name]);
                        } else {
                            $this->_createActionFromTreeAttrib($name, $attr->nodeValue);
                            $node->removeAttributeNode($attr);
                        }
                }
            }
        }

        // Add the new default attributes
        foreach($newAttributes as $name => $value) {
            $node->setAttribute($name, $value);
        }
    }

    /**
     * checks if it is necessary to create an ACTION from this property name and value
     *  ** ACTION will be listed in $_newActions
     * 
     * @access private
     * @param  string $name
     * @param  string $value
     * @return void
     */
    private function _createActionFromTreeAttrib($name, $value) {
        if (strtolower(substr($name, -3)) !== 'url') {
            return;
        }
        $name = substr($name, 0, -3);

        // split camelcase old action name
        $splittedName = preg_split('/(?=[A-Z][^A-Z]+$)/', $name);

        /*
         *  Should be 2 elements in $splittedName
         *   ** Action Name
         *   ** Action Context
         */
        if (count($splittedName) < 2) {
            return;
        }

        // check if context is valid
        $context = end($splittedName);
        switch ($context) {
        case 'Class':
        case 'Instance':
        case 'Resource':
            break;
        default:
            return;
        }

        // Action name
        array_pop($splittedName);
        $action = implode(' ', $splittedName);

        //Action id
        $id = implode('_', strtolower($splittedName));

        // List in array
        $this->_newActions[strtolower($action)] = array(
            'id'      => $id,
            'name'    => ucfirst($action),
            'url'     => $value,
            'context' => strtolower($context),
            'group'   => 'DETERMINE_GROUP'
        );
    }

    /**
     * updates the ACTION node
     *
     * @access private
     * @param  domnode $node
     * @return void
     */
    private function actionNodeUpdate(DOMNode &$action) {
        // TODO: determine the source of new group attribute value
        $group = 'DETERMINE_GROUP';
        $action->setAttribute('group', $group);

        $icon = $action->getAttribute('name');
        $this->_oldActions[strtolower($icon)] = 1;
        if (empty($icon)) {
            $icon = trim($action->getAttribute('url'), '/');
        }

        // append the icon child element
        $action->appendChild(
            $this->_createNode('icon', array(
                'id' => 'icon-' . preg_replace('/[^a-z0-9]/', '-', strtolower($icon))
            ))
        );
    }

    /**
     * Create new actions
     */
    private function placeNewActions() {
        if (empty($this->_actions) || empty($this->_newActions)) {
            return;
        }

        foreach($this->_newActions as $name => $values) {
            if (!isset($this->_oldActions[$name])) {
                $action = $this->_createNode('action', $values);
                $action->appendChild(
                    $this->_createNode('icon', array('id' => 'icon-' . $name))
                );
                $this->_actions->appendChild($action);
            }
        }
    }

    /*
     * Creates a new element with a list properties
     */
    private function _createNode($tag, $attributes = array()) {
        $element = $this->_doc->createElement($tag);
        foreach($attributes as $name => $value) {
            $element->setAttribute($name, $value);
        }
        return $element;
    }

    public function run() {
        $source = ROOT_PATH . 'taoUpdate/test/sample/migrate/structures_old.xml';
        $dest = ROOT_PATH . 'taoUpdate/test/sample/migrate/structures_new.xml';

        $this->_doc = new DOMDocument();
        $this->_doc->load($source);

        $this->exploreNode($this->_doc);
        $this->placeNewActions();

        $buffer = $this->_doc->saveXML();

        if(!file_put_contents($destination, $buffer)){
            throw new tao_install_utils_Exception(
                "An error occured while writing structures.xml file '${dest}'."
            );
        }
    }
}