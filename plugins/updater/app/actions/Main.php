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
 * @package package_name
 * @subpackage 
 *
 */
namespace app\actions;

use app\models\UpdateService;
use app\scripts\OldVersionRemover;
use OatBox\Common\Uri;
use app\scripts\OldVersionArchiver;
use app\scripts\Test;
use OatBox\Common\Logger;
use app\scripts\OldVersionRestorer;

class Main extends \OatBox\Controller\Module {
    
    private $service;
    
    public function __construct(){
        $this->service = UpdateService::getInstance();
    }
    

    public function test(){
        $parameters = array();
        
        $options = array(
            'argv' => array(0 => 'Script OldVersionRestorer'),
            'output_mode' => 'log_only'
        );
        try {
            new OldVersionRestorer(array('parameters' => $parameters),$options );
        }
        catch(\Exception $e){
            Logger::e('Error occurs during update ' . $e->getMessage());
        }
        $this->setData('ROOT_URL',ROOT_URL);
        $this->setView('logViewer.tpl');
    }
    
    public function index() {
        
        if (!$this->hasRequestParameter('key')) {
            $this->redirect(Uri::url('maintenance'));
        }
        $key = $this->getRequestParameter('key');
        if(!UpdateService::isAllowed($key)){
            $this->redirect(Uri::url('maintenance'));
        }
        else{

            
           //echo 'Start Upgrading TAO';
           
           $parameters = array();

           $options = array(
               'argv' => array(0 => 'Script OldVersionRemover'),  
               'output_mode' => 'log_only'
           );
           try {
           new OldVersionArchiver(array('parameters' => $parameters),$options );
           }
           catch(\Exception $e){
               Logger::e('Error occurs during update ' . $e->getMessage());
           }
           $this->setData('ROOT_URL',ROOT_URL);
           $this->setView('logViewer.tpl');
          
        }
       
        
    }
    
    
    public function maintenance() {
        $this->setView('maintenance.tpl');;
        
    }
    
}