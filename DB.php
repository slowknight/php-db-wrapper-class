<?php

class DB 
{   
    protected $_conn;
    protected $_query;

    public function __construct($host, $dbname, $username, $passphrase)
    {
        try {
            $str = "mysql:host=" . $host . ";dbname=" . $dbname;
            $this->_conn = new PDO($str, $username, $passphrase);
        } catch (PDOException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    
    /**
     * Executes the passed in query and returns an array of rows
     * 
     * @param string $query Query string
     * @return array Array of Results
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
    
    public function get($tableName, $numRows)
    {
        
    }

    public function __destruct() {
        // Close connection to db
    }
}

?>