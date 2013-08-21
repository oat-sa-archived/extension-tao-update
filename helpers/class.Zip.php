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
class taoUpdate_helpers_Zip
{

    /**
     * @access
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $file
     * @param string $dest
     */
    public static function extractFile($file, $dest){
        $zip = new ZipArchive();
        $zip->open($file);
        $zip->extractTo($dest);
        $zip->close();
    }

    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $src
     * @param string $dest
     */
    public static function compressFile($src, $dest){
        $pathInfo = pathInfo($src);
        $fileName = $pathInfo['basename'];
        $z = new ZipArchive();
        $z->open($dest, ZipArchive::OVERWRITE);
        if (is_file($src)) {
            $z->addFile($src,$fileName);
        }
        $z->close();
    }
    
    /**
     * @access public
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $src
     * @param string $dest
     * @param string $includeDir
     */
    public static function compressFolder($src, $dest,$includeDir = false)
    {
        $pathInfo = pathInfo($src);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];
        $z = new ZipArchive();
        $z->open($dest, ZipArchive::OVERWRITE);
        $exclusiveLength = strlen("$src/") ;
        if($includeDir){
            $z->addEmptyDir($dirName);           
            $exclusiveLength = strlen("$parentPath/");
        }
        self::folderToZip($src, $z, $exclusiveLength);
        $z->close();
    }
    
    /**
     * @access private
     * @author "Lionel Lecaque, <lionel@taotesting.com>"
     * @param string $folder
     * @param string $zipFile
     * @param string $exclusiveLength
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..' && $f != '.svn') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
    
    
}