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
            $this->dump .= $tbl['create'] . "\n";
            $this->dump .= '--INSERTING DATA INTO ' . $tbl['name'] . "\n";
            $this->dump .= $tbl['data'] . "\n\n\n";
        }
        
        $this->dump .= '-- STORE PROCEDURE ' . "\n";
        if ($this->dbWrapper instanceof core_kernel_classes_PgsqlDbWrapper) {
            if(is_file(BASE_PATH . '../tao/install/db/tao_stored_procedures_pgsql.sql')) {
                $this->dump .= file_get_contents(BASE_PATH . '/../tao/install/db/tao_stored_procedures_pgsql.sql');
            }
        } else {
            if(is_file(BASE_PATH . '/../tao/install/db/tao_stored_procedures_mysql.sql')) {
                $this->dump .= file_get_contents(BASE_PATH . '/../tao/install/db/tao_stored_procedures_mysql.sql');
            }
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
                    if ($this->dbWrapper instanceof core_kernel_classes_PgsqlDbWrapper) {
                        $value = $this->dbWrapper->quote($value);
                    }
                    else {
                        $value = htmlentities(addslashes($value));
                    }

                }
                $data .= 'INSERT INTO ' . $tableName . ' VALUES (' . implode(',', $pieces) . ');' . "\n";
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
            if ($this->dbWrapper instanceof core_kernel_classes_PgsqlDbWrapper) {
                return $this->generatePgSqlCreate($tableName);
            } else {
                $stmt = $this->dbWrapper->query('SHOW CREATE TABLE ' . $tableName);
                $q = $stmt->fetchAll();
                $q[0][1] = preg_replace("/AUTO_INCREMENT=[\w]*./", '', $q[0][1]);
                return $q[0][1] . ";";
            }
            // return $this->dbWrapper->getColumnNames($tableName);
        } catch (PDOException $e) {
            $this->error[] = $e->getMessage();
            return false;
        }
    }

    private function generatePgSqlCreate($tableName)
    {
        $squenceQuery = "";
        $tableQuery = "CREATE TABLE " . $tableName . " (";
        $sequences = array();
        
        $query = "SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, adsrc AS def\n" 
            . "FROM pg_attribute, pg_class, pg_type, pg_attrdef\n" 
            . "WHERE pg_class.oid=attrelid\n" 
            . "AND pg_type.oid=atttypid AND attnum>0 AND pg_class.oid=adrelid AND adnum=attnum\n" 
            . "AND atthasdef='t' AND lower(relname)='" . $tableName . "' UNION\n" 
            . "SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, '' AS def\n" 
            . "FROM pg_attribute, pg_class, pg_type WHERE pg_class.oid=attrelid\n" 
            . "AND pg_type.oid=atttypid AND attnum>0 AND atthasdef='f' AND lower(relname)='" . $tableName . "' ORDER BY attnum \n";
        
        $results = $this->dbWrapper->query($query);
        $columns = $results->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($tableName, $columns);
        $columnsMap = array();
        foreach ($columns as $column) {
            
            if (preg_match("/^nextval/", $column['def'])) {
                $t = explode("'", $column['def']);
                $sequences[] = $t[1];
            }

            $columnsMap[$column['attnum']] = '"' . $column['attname'] . '"';
            $tableQuery .= '"'. $column['attname'] .'" '. $column['typname'];
            if ($column['typname'] == "varchar") {
                $tableQuery .= "(" . $column['atttypmod'] . ")";
            }
            if ($column['attnotnull'] === true) {
                $tableQuery .= " NOT NULL";
            }
            if ($column['atthasdef'] === true) {
                $tableQuery .= " DEFAULT " . $column['def'];
            }
            $tableQuery .= ",";
        }
        
        $tableQuery = rtrim($tableQuery, ",");
        $tableQuery .= ");\n";
        
        if (! empty($sequences)) {
            
            foreach ($sequences as $seq) {
                
                $query = "SELECT * FROM " . $seq . "\n;";
                $results = $this->dbWrapper->query($query);
                $values = $results->fetchAll(PDO::FETCH_ASSOC);
                $values = $values[0];
                $squenceQuery .= 
                    "CREATE SEQUENCE " . $seq . " INCREMENT " . $values['increment_by'] 
                    . " MINVALUE " . $values['min_value'] . " MAXVALUE " . $values['max_value'] 
                    . " START " . $values['last_value'] . " CACHE " . $values['cache_value'] . ";\n";
            }
        }
        
        
        $constaintQuery = '-- CREATE CONSTRAINT' . "\n\n";
        
        $queryConstraintName = "SELECT relname FROM pg_class WHERE oid IN (
                        SELECT indexrelid
                          FROM pg_index, pg_class
                         WHERE pg_class.relname='". $tableName . "'
                           AND pg_class.oid=pg_index.indrelid
                           AND (   indisunique = 't'   OR indisprimary = 't' )
                 )";
        
        $query = "SELECT c.conname AS constraint_name,
                      CASE c.contype
                        WHEN 'c' THEN 'CHECK'
                        WHEN 'f' THEN 'FOREIGN KEY'
                        WHEN 'p' THEN 'PRIMARY KEY'
                        WHEN 'u' THEN 'UNIQUE'
                      END AS \"constraint_type\",
                      array_to_string(c.conkey, ' ') AS constraint_key

                 FROM pg_constraint c
            LEFT JOIN pg_class t  ON c.conrelid  = t.oid
            LEFT JOIN pg_class t2 ON c.confrelid = t2.oid
                WHERE t.relname = '". $tableName ."'
                 AND c.conname in (" . $queryConstraintName . ");";
        
        $results = $this->dbWrapper->query($query);
        $values = $results->fetchAll(PDO::FETCH_ASSOC);
        $keysString = array();
        $keyNumArray = explode(" ",$values[0]['constraint_key']);
        
        foreach ($keyNumArray as $k){
            $keysString[] = $columnsMap[$k];
        }

        $constaintQuery .= "ALTER TABLE ONLY " . $tableName . " ADD CONSTRAINT " . $values[0]['constraint_name']
                            . " " . $values[0]['constraint_type'] . "(" . implode(',',$keysString) . ");" . "\n\n";

  
        
        return $squenceQuery. $tableQuery .  $constaintQuery;
    }
}