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
 * @package OatBox\Common\Helpers
 *
 */
namespace OatBox\Common\Helpers;

use OatBox\Common\Exception;
use OatBox\Common\Logger;

class File
{


    /**
     * Remove a file.
     * If the recursive parameter is set to true, the target file
     * can be a directory that contains data.
     *
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param string path The path to the file you want to remove.
     * @param boolean recursive (optional, default is false) Remove file content recursively (only if the path points to a directory).
     * @return boolean Return true if the file is correctly removed, false otherwise.
     */
    public static function remove($path, $recursive = false)
    {
        $returnValue = (bool) false;
        
        if ($recursive) {
            
            if (is_file($path)) {
                $returnValue = unlink($path);
            } elseif (is_dir($path)) {
                
                $handle = opendir($path);
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        self::remove($path . DIRECTORY_SEPARATOR . $entry);
                    }
                }
                closedir($handle);
                
                $returnValue = rmdir($path);
            } else {
                throw new Exception('"' . $path . '" cannot be removed since it\'s neither a file nor directory');
            }
        } elseif (is_file($path)) {
            $returnValue = @unlink($path);
        }
        // else fail silently
        
        return (bool) $returnValue;
    }

    /**
     * Move file from source to destination.
     *
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param string source A path to the source file.
     * @param string destination A path to the destination file.
     * @return boolean Returns true if the file was successfully moved, false otherwise.
     */
    public static function move($source, $destination,$ignoreSystemFiles = true)
    {
        $returnValue = (bool) false;
       
        if (is_dir($source)) {
            if (! file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $error = false;
            foreach (scandir($source) as $file) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($source . '/' . $file)) {
                        if (! self::move($source . '/' . $file, $destination . '/' . $file, $ignoreSystemFiles)) {
                            $error = true;
                        }
                    } else {
                        if (! self::copy($source . '/' . $file, $destination . '/' . $file, true,$ignoreSystemFiles)) {
                            $error = true;
                        }
                    }
                }
            }
            if (! $error) {
                $returnValue = true;
            }
            self::remove($source, true);
        } else {
            if (file_exists($source) && file_exists($destination)) {
                $returnValue = rename($source, $destination);
            } else {
                if (self::copy($source, $destination, true,$ignoreSystemFiles)) {
                    $returnValue = self::remove($source);
                }
            }
        }
        
        return (bool) $returnValue;
    }

    /**
     * Copy a file from source to destination, may be done recursively and may ignore system files
     *
     * @access public
     * @author Lionel Lecaque, <lionel@taotesting.com>
     * @param string source
     * @param string destination
     * @param boolean recursive
     * @param boolean ignoreSystemFiles
     * @return boolean
     */
    public static function copy($source, $destination, $recursive = true, $ignoreSystemFiles = true)
    {
        $returnValue = (bool) false;
        
        // Check for System File
        $basename = basename($source);
        if ($basename[0] == '.' && $ignoreSystemFiles == true) {
            return false;
        }
        
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $destination);
        }
        
        // Simple copy for a file
        if (is_file($source)) {
            // get path info of destination.
            $destInfo = pathinfo($destination);
            if (isset($destInfo['dirname']) && ! is_dir($destInfo['dirname'])) {
                if (! mkdir($destInfo['dirname'], 0777, true)) {
                    return false;
                }
            }
            
            return copy($source, $destination);
        }
        
        // Make destination directory
        if ($recursive == true) {
            if (! is_dir($destination)) {
                // 0777 is default. See mkdir PHP Official documentation.
                mkdir($destination, 0777, true);
            }
            
            // Loop through the folder
            $dir = dir($source);
            while (false !== $entry = $dir->read()) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                
                // Deep copy directories
                self::copy("${source}/${entry}", "${destination}/${entry}", $recursive, $ignoreSystemFiles);
            }
            
            // Clean up
            $dir->close();
            return true;
        } else {
            return false;
        }
        
        return (bool) $returnValue;
    }
}