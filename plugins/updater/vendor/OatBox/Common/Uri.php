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
namespace OatBox\Common;

use OatBox\Controller\Context;

class Uri{
    
    private static $base = null;
    
    /**
     * @access private
     * @var mixed
     */
    private static $root = null;
    
    /**
     * conveniance method to create urls based on the current MVC context and
     * it for the used kind of url resolving
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string action
     * @param  string module
     * @param  string extension
     * @param  array params
     * @return string
     */
    public static function url($action = null, $module = null, $extension = null, $params = array())
    {
        $returnValue = (string) '';
    
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 begin
    
        $context = Context::getInstance();
        if(is_null($module)){
            $module = $context->getModuleName();
        }
        if(is_null($action)){
            $action = $context->getActionName();
        }
    
        if(is_null($extension) || empty($extension)){
            $returnValue = self::getBaseUrl() . $module . '/' . $action;
        }
        else{
            $returnValue = self::getRootUrl(). $extension . '/'. $module . '/' . $action;
        }
    
        if(count($params) > 0){
            $returnValue .= '?';
            if(is_string($params)){
                $returnValue .= $params;
            }
            if(is_array($params)){
                foreach($params as $key => $value){
                    $returnValue .= $key . '=' . urlencode($value) . '&';
                }
                $returnValue = substr($returnValue, 0, -1);
            }
        }
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A26 end
    
        return (string) $returnValue;
    }
    
    /**
     * get the project base url
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getBaseUrl()
    {
        $returnValue = (string) '';
    
        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 begin
    
        if(is_null(self::$base) && defined('BASE_URL')){
            self::$base = BASE_URL;
            if(!preg_match("/\/$/", self::$base)){
                self::$base .= '/';
            }
        }
        $returnValue = self::$base;

        // section 127-0-1-1-4955a5a0:1242e3739c6:-8000:0000000000001A45 end
    
        return (string) $returnValue;
    }
}