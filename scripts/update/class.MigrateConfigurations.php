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
 * @author lionel
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */

class taoUpdate_scripts_update_MigrateConfigurations extends tao_scripts_Runner {

    const CONFIG_FILE = 'configFiles.php';

    public function run() {

        $writer = new taoUpdate_helpers_ConfigWriter(ROOT_PATH);

        // list of config files to be migrated
        $configs = include(BASE_DATA . self::CONFIG_FILE);

        foreach($configs as $config) {

            $name = "get{$config['name']}Config";

            // write config
            $writer->write(
                $this->$name(), $config['title'], $config['descrip'],
                $config['path'], $config['type'],
                isset($config['prepend']) ? $config['prepend'] : ''
            );
        }

        die();
    }

    /**
     * 
     * @return array
     */
    public function getPersistencesConfig() {
        if (!isset($GLOBALS['generis_persistences'])) {
            throw new taoUpdate_models_classes_UpdateException('$GLOBALS[generis_persistences] does not exist');
        }

        $returnValue = $GLOBALS['generis_persistences'];
        $returnValue['cache']['driver'] = $returnValue['config']['driver'];

        unset($returnValue['config']);

        return $returnValue;
    }

    /**
     * 
     * @return array
     */
    public function getAuthConfig() {
        return array(
            array(
                'driver' => 'oat\\generis\\model\\user\\AuthAdapter',
                'hash'   => array(
                    'algorithm' => PASSWORD_HASH_ALGORITHM,
                    'salt'      => PASSWORD_HASH_SALT_LENGTH,
                )
            )
        );
    }

    /**
     * 
     * @return array
     */
    public function getInstallationConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/generis_extension.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getOntologyConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/generis_oat_generis_model.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getFileSystemAccessConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/tao_filesystemAccess.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getDefaultUploadFileSourceConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/tao_defaultUploadFileSource.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getFuncAccessControlConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/tao_AclImplementation.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getServiceFileStorageConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/tao_ServiceFileStorage.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getExecutionServiceConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/taoDelivery_delivery_execution_id.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getDefaultItemFileSourceConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/taoItems_defaultItemFileSource.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getQtiAcceptableLatencyConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/taoQtiTest_qtiAcceptableLatency.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getQtiTestFolderConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/taoQtiTest_qtiTestFolder.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getDefaultResultServerConfig() {
        $path = ROOT_PATH . 'generis/data/generis/config/taoResultServer_default_resultserver.php';

        if (!file_exists($path)) {
            return null;
        }

        $config = include($path);

        return $config;
    }

    /**
     * 
     * @return array
     */
    public function getGenerisConfig() {
        $path = ROOT_PATH . 'generis/common/conf/generis.conf.php';

        if (!file_exists($path)) {
            return null;
        }

        $returnValue = '';
        $excludes = array('INCLUDES_PATH', 'FILES_PATH', 'PROFILING', '# profiling');
        $filesPath = ROOT_PATH;

        $hfile = fopen($path, "r");

        if ($hfile) {

            while (($line = fgets($hfile)) !== false) {

                foreach($excludes as $exclude) {
                    if (strpos($line, $exclude) !== false) {
                        $line = '';
                        break;
                    }
                }

                if (strpos($line, '#') === 0) {

                    $returnValue .= PHP_EOL . $line;
                    
                    if (stripos($line, 'generis paths') !== false) {
                        $returnValue .= "define('FILES_PATH','" . addslashes($filesPath) . "data\\\');";
                        $filesPath = false;
                    }

                } elseif (stripos($line, 'define') !== false) {

                    $returnValue .= str_replace('GENERIS_BASE_PATH', 'ROOT_PATH', $line);

                }
            }

        }

        fclose($hfile);

        if ($filesPath !== false) {
            $returnValue .= '#generis paths' . PHP_EOL . "define('FILES_PATH','{$filesPath}data\\');";
        }

        return $returnValue;
    }

    /**
     * 
     * @return array
     */
    public function getSimpleAclWhitelistConfig() {

        return array(
            'tao_actions_Main' => array(
                'entry' => '*',
                'login' => '*',
                'logout' => '*',
            ),
            'tao_actions_AuthApi' => '*',
            'tao_actions_ClientConfig' => '*',
        );
    }

    /**
     * 
     * @return array
     */
    public function getQtiItemHookConfig() {

        return array(
            'pciCreator' => 'oat\\qtiItemPci\\model\\CreatorHook',
        );
    }

    /**
     * 
     * @return array
     */
    public function getQtiItemLibrariesConfig() {

        return array(
            'IMSGlobal/jquery_2_1_1' => ROOT_URL . 'taoQtiItem/views/js/portableSharedLibraries/IMSGlobal/jquery_2_1_1.js',
            'OAT/lodash'             => ROOT_URL . 'taoQtiItem/views/js/portableSharedLibraries/OAT/lodash.js',
            'OAT/async'              => ROOT_URL . 'taoQtiItem/views/js/portableSharedLibraries/OAT/async.js',
            'OAT/raphael'            => ROOT_URL . 'taoQtiItem/views/js/portableSharedLibraries/OAT/raphael.js',
            'OAT/scale.raphael'      => ROOT_URL . 'taoQtiItem/views/js/portableSharedLibraries/OAT/scale.raphael.js',
        );
    }

    /**
     * 
     * @return array
     */
    public function getLogConfig() {
        return array();
    }

    /**
     * 
     * @return array
     */
    public function getMailConfig() {
        return array(
            'SMTP_HOST' => SMTP_HOST,
            'SMTP_PORT' => SMTP_PORT,
            'SMTP_AUTH' => SMTP_AUTH,
            'SMTP_USER' => SMTP_USER,
            'SMTP_PASS' => SMTP_PASS,
        );
    }

    /**
     * 
     * @return array
     */
    public function getPermissionsConfig() {
        return 'oat\\generis\\model\\data\\permission\\implementation\\FreeAccess';
    }

    /**
     * 
     * @return array
     */
    public function getProfilerConfig() {
        $path = ROOT_PATH . 'generis/common/conf/default/profiler.conf.php';

        if (!file_exists($path)) {
            return null;
        }

        include($path);

        return
            '//default profilers config:' . PHP_EOL . '$defaultConfig = ' . var_export($defaultConfig, 1) . ';' . str_repeat(PHP_EOL, 3) . 
            '//archivers config:'         . PHP_EOL . '$ftpArchiver = ' . var_export($ftpArchiver, 1) . ';' . PHP_EOL .
            '$udpArchiver = ' . var_export($udpArchiver, 1) . ';' . PHP_EOL . 'return array();';
    }

}