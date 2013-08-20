<?php

class taoUpdate_helpers_DbBackup
{

    private $dbWrapper = null;

    private $error = array();

    private $dump;

    public function __construct()
    {
        $this->dbWrapper = core_kernel_classes_DbWrapper::singleton();
        $this->getTables();
        $this->generate();
    }

    public function backup()
    {
        if (count($this->error) > 0) {
            common_Logger::e('Fail to dump database');
            
            return false;
        }
        return $this->dump;
    }

    private function generate()
    {
        foreach ($this->tables as $tbl) {
            $this->dump .= '--CREATING TABLE ' . $tbl['name'] . "\n";
            $this->dump .= $tbl['create'] . ";\n\n";
            $this->dump .= '--INSERTING DATA INTO ' . $tbl['name'] . "\n";
            $this->dump .= $tbl['data'] . "\n\n\n";
        }
        $this->dump .= '-- THE END' . "\n\n";
    }

    private function getTables()
    {
        try {
            $tbs = $this->dbWrapper->getTables();
            
            $i = 0;
            foreach ($tbs as $table) {
                $this->tables[$i]['name'] = $table;
                $this->tables[$i]['create'] = $this->getColumns($table);
                $this->tables[$i]['data'] = $this->getData($table);
                $i ++;
            }
            
            return true;
        } catch (PDOException $e) {
            $this->error[] = $e->getMessage();
            return false;
        }
    }

    private function getData($tableName)
    {
        try {
            $stmt = $this->dbWrapper->query('SELECT * FROM ' . $tableName);
            $q = $stmt->fetchAll(PDO::FETCH_NUM);
            $data = '';
            foreach ($q as $pieces) {
                foreach ($pieces as &$value) {
                    $value = htmlentities(addslashes($value));
                }
                $data .= 'INSERT INTO ' . $tableName . ' VALUES (\'' . implode('\',\'', $pieces) . '\');' . "\n";
            }
            return $data;
        } catch (PDOException $e) {
            $this->error[] = $e->getMessage();
            return false;
        }
    }

    private function getColumns($tableName)
    {
        try {
            $stmt = $this->dbWrapper->query('SHOW CREATE TABLE ' . $tableName);
            $q = $stmt->fetchAll();
            $q[0][1] = preg_replace("/AUTO_INCREMENT=[\w]*./", '', $q[0][1]);
            return $q[0][1];
            // return $this->dbWrapper->getColumnNames($tableName);
        } catch (PDOException $e) {
            $this->error[] = $e->getMessage();
            return false;
        }
    }
}