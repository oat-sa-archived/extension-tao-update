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
 * Copyright (c) $2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package taoUpdate
 * @subpackage helpers
 *
 */

class taoUpdate_helpers_Optimization{

    /**
     * 
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     */
    public static function isDesignModeEnabled(){
        $returnValue = true;
        $optimizableClasses = tao_helpers_Optimization::getOptimizableClasses();
        foreach ($optimizableClasses as $class){
            if(isset($class['status'])){
                $returnValue &= $class['status'] == tao_helpers_Optimization::DECOMPILED;
            }
            else{
                common_Logger::e('Problem occcurs when checking if design mode enable');
                return false;
            }
        }
       return $returnValue;
    }
    
}