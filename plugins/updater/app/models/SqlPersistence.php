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
namespace app\models;

use OatBox\Common\Logger;

class SqlPersistence
{

    private $connection = null;

    private $modelid = array(
        3 => 4,
        4 => 3,
        5 => 2,
        6 => 7,
        7 => 6,
        8 => 1,
        9 => 13,
        10 => 11,
        11 => 15,
        12 => 14,
        13 => 17,
        14 => 12,
        15 => 8,
        17 => 5,
        18 => 18,
        19 => 10,
        20 => 16
    )
    ;

    public function __construct()
    {
        $releaseManifest = UpdateService::getInstance()->getReleaseManifest();
        $oldPath = $releaseManifest['old_root_path'];
        require_once $oldPath . 'generis/vendor/autoload.php';
        require_once $oldPath . 'generis/common/conf/db.conf.php';
        
        $connectionParams = array(
            'driver' => SGBD_DRIVER,
            'host' => DATABASE_URL,
            'dbname' => DATABASE_NAME,
            'user' => DATABASE_LOGIN,
            'password' => DATABASE_PASS
        );
        
        $config = new \Doctrine\DBAL\Configuration();
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public function migrateDb()
    {
        $sm = $this->connection->getSchemaManager();
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('m.modelid', 'm.modeluri')->from('models', 'm');
        // modelid
        $result = $this->connection->executeQuery($queryBuilder->getSql());
        $namespaces = array();
        $newNs = array();
        while ($row = $result->fetch()) {
            $id = $row['modelid'];
            $uri = $row['modeluri'];
            if(substr($uri,-1) != '#'){
                $uri .= '#' ;
            }
            $namespaces[$id] = $uri;
            $newNs[$this->modelid[$id]] = $uri;
        }
        ksort($newNs);
        
        $query = 'ALTER TABLE statements CHANGE ' . $this->connection->quoteIdentifier('modelID') . ' ' . $this->connection->quoteIdentifier('modelid') . ' INT( 11 )';
        $result = $this->connection->executeUpdate($query);
        
        $schema = $sm->createSchema();
        $newSchema = clone $schema;
        $newSchema->dropTable('models');
        $sql = $schema->getMigrateToSql($newSchema, $this->connection->getDatabasePlatform());
        
        foreach ($sql as $q) {
            $result = $this->connection->executeUpdate($q);
        }
        
        $schema = $sm->createSchema();
        $newSchema = clone $schema;
        $table = $newSchema->createTable("models");
        $table->addColumn('modelid', "integer", array(
            "notnull" => true
        ));
        $table->addColumn('modeluri', "string", array(
            "length" => 255,
            "default" => null
        ));
        $table->addOption('engine', 'MyISAM');
        $table->setPrimaryKey(array(
            'modelid'
        ));
        $table->addIndex(array(
            'modeluri'
        ), "idx_models_modeluri");
        
        $sql = $schema->getMigrateToSql($newSchema, $this->connection->getDatabasePlatform());
        
        foreach ($sql as $q) {
            $result = $this->connection->executeUpdate($q);
        }
        
        foreach ($newNs as $id => $ns) {
            $result = $this->connection->insert('models', array(
                'modelid' => $id,
                'modeluri' => $ns
            ));
        }
        $orderId = $this->modelid;
        asort($orderId);
        
        $sanatyCheck = array();
        foreach ($orderId as $old => $new) {
            $tmpvalue = 100 + $new;
            $query = 'update statements set modelid = ' . $tmpvalue . ' where modelid=' . $old;
            $result = $this->connection->executeUpdate($query);
            $sanatyCheck[$newNs[$new]]['first'] = $result;
        }
        for ($i = 101; $i < 120; $i ++) {
            $tmpvalue = $i - 100;
            $query = 'update statements set modelid = ' . $tmpvalue . ' where modelid=' . $i;
            $result = $this->connection->executeUpdate($query);
            if (isset($newNs[$tmpvalue])) {
                $sanatyCheck[$newNs[$tmpvalue]]['second'] = $result;
            }
        }
        // var_dump($sanatyCheck);
        Logger::d('Update database completed');
        // echo 'plop';
    }
}