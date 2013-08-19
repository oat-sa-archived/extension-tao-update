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
 * @package taoUpdate24
 * @subpackage helpers
 *
 */

class taoUpdate24_helpers_Zip{

  
    
    public static function addFile($src, $dest) {
        
        //todo check right
        $zip = new ZipArchive();
        
         if($zip->open($dest, ZipArchive::CREATE) !== true){
            throw new Exception('Unable to create archive at '.$dest);
        }
        
        $all= new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src));
        
        foreach ($all as $f=>$value) {
            
            if(strpos($f, '.svn') != false) {
                continue;
            }
           
            $zip->addFile(realpath($f), $f) or die ("ERROR: Unable to add file: $f");
        }
        $zip->close();

    }
    
    

}