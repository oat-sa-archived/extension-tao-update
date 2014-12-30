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
 */

/**
 * The ConfigWriter class enables you to create config file from kv array
 * in result it will write constants
 */

class taoUpdate_helpers_ConfigWriter
{

    const CONFIG_TYPE_ARRAY = 1;
    const CONFIG_TYPE_STRING = 2;

    const FILE_TYPE_PHP = 1;

    protected $basePath;
    
    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    private function createPath($array) {
        $start = $this->basePath;

        foreach($array as $path) {
            $start .= $path . '/';
            if (!file_exists($start)) {
                mkdir($start, 0777, true);
            }
        }

    }

    /**
     * Create the config file from the sample
     * 
     * @param array $config
     * @param string $title
     * @param string $descrip
     * @param string $path
     * @param integer $type
     * @param string $prepend
     * @throws taoUpdate_models_classes_UpdateException
     */
    public function write($config, $title,  $descrip, $path, $type, $prepend) {

        // Create path tree
        $array = preg_split('_[\\\\/]_', $path);
        array_pop($array);
        $this->createPath($array);

        if (file_exists($this->basePath . $path)) {
            @unlink($this->basePath . $path);
//            throw new taoUpdate_models_classes_UpdateException("Config file '$this->basePath$path' already exists");
        }

        $hfile = fopen($this->basePath . $path, 'w');

        if (!$hfile) {
            throw new taoUpdate_models_classes_UpdateException("Failure creating file '$this->basePath$path', please check permissions");
        }

        // write license headers
        $lines = array(
            '<?php',
            '/**',
            " * $title config",
            ' *',
            " * $descrip",
            ' *',
            ' * @author Open Assessment Technologies SA',
            ' * @package ' . end($array),
            ' * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php',
            ' */'
        );

        foreach($lines as $line){
            fwrite($hfile, $line . PHP_EOL);
        }

        switch($type) {
        case self::CONFIG_TYPE_ARRAY:
            $config = var_export($config, true);
            break;
        case self::CONFIG_TYPE_STRING:
            break;
        default:
            die('INVALID CONFIG TYPE');
        }

        fwrite($hfile, $prepend . $config . ';');

        fclose($hfile);
    }

	/**
	 * 
	 * @throws tao_install_utils_Exception
	 */
	public function writeConstants($filePath, array $array)
	{
        $hfile = fopen($this->basePath . $filePath, 'w');

        foreach($array as $key => $value) {
            fwrite($hfile, "define('$key','$value');" . PHP_EOL);
        }

        fclose($hfile);
	}

}