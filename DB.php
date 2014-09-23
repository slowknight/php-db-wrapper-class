<?php

class DB 
{   
    protected $_conn;
    protected $_query;
    protected $_where = array();

    public function __construct($host, $dbname, $username, $passphrase)
    {
        try {
            $str = "mysql:host=$host;dbname=$dbname";
            $this->_conn = new PDO($str, $username, $passphrase);
        } catch (PDOException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     * Executes the passed in query and returns an array of rows
     * 
     * @param string $query Query string
     * @return array Array of arrays
     */
    public function query($query)
    {
        $this->_query = filter_var($query, FILTER_SANITIZE_STRING);
        $stmt = $this->_prepareQuery();
        $stmt->execute();
        $results = $this->_bindResults($stmt);
        return $results;
    }
    
    protected function _prepareQuery()
    {
        if ( !$stmt = $this->_conn->prepare($this->_query) ) {
            trigger_error("A problem occured while attempting to prepare query.", E_USER_ERROR);
        }
        return $stmt;
    }
    
    protected function _bindResults($stmt)
    {
        $results = array();
        while ( $row = $stmt->fetch() ) {
            $results[] = $row;
        }
        return $results;
    }
    
    /**
     * Helper method which performs a SELECT statement on the passed in
     * table and returns an array of rows
     * 
     * @param string $tableName Name of the table
     * @param integer $numRows Number of rows to be returned (optional)
     * 
     * @return array Array of arrays
     */
    public function get($tableName, $numRows = NULL)
    {
        $this->_query = "SELECT * FROM $tableName";
        
        if ( !empty($this->_where) ) {
            $keys = array_keys($this->_where);
            $col = $keys[0];
            $this->_query .= " WHERE " . $col . " = ?";
        }
        
        if ( gettype($numRows) === "integer" ) {
            $this->_query .= " LIMIT " . $numRows;
        }
        
        $stmt = $this->_prepareQuery();
        
        if ( $this->_where ) {
            $stmt->bindParam(1, $this->_where[$col]);
        }
        
        $stmt->execute();
        
        $results = $this->_bindResults($stmt);
        return $results;
    }
    
    public function whereClause($prop, $value)
    {
        $this->_where[$prop] = $value;
    }
    
    public function getMetadata($tableName)
    {
        return $this->query("DESCRIBE $tableName");
    }
    
    /**
     * Helper method which inserts data described in the passed in argument into the passed in table
     * 
     * @param string $tableName Name of the table
     * @param array $tableData Data to be inserted as an associative array: array(field => value)
     * 
     * @return boolean true if insert successful; false otherwise
     */
    public function insert($tableName, $tableData) 
    {   
        if( gettype($tableData) != "array" )
            return false;
        if( gettype($tableName) != "string" )
            return false;
        
        //build query 
        /* TODO : Looks like building query might be something to be replicated, might add build_query() func*/
        
        $this->_query = "INSERT INTO $tableName";
        
        $keys = array_keys($tableData);
        $this->_query .= "(" . implode(", ", $keys) . ") VALUES (";
        for ( $i = 0; $i < count($tableData); $i++ ) {
            $this->_query .= "?";
            if( $i != count($tableData) - 1 ) {
                $this->_query .= ",";
            }
        }
        $this->_query .= ")";
        
        //prepare it
        $stmt = $this->_prepareQuery($this->_query);
        $count = 0;
        $values = array_values($tableData);
        
        array_map(function($param) use (&$stmt, &$count) {
            $count ++;
            $stmt->bindParam($count, $param);
            echo "$count, $param";
        }, $values);
        
        return $stmt->execute();
    }

    public function __destruct() 
    {
        // Close connection to db
    }
}

