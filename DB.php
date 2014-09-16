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
     * Helper function which performs a SELECT statement on the passed in
     * table and returns an array of rows
     * 
     * @param type $tableName Table name
     * @param type $numRows Number of rows to be returned (optional)
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

    public function __destruct() {
        // Close connection to db
    }
}

?>