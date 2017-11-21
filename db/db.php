<?php
class DB {
    /**
     * Method to connect mysql database and select database
     * @return void
     */
    function __construct() {
        global $db;
        $this->db_conn = mysql_connect(DB_HOST, DB_USER, DB_PASS);            
        if ($this->db_conn) {
            if (!mysql_select_db(DB_NAME)) {
                throw new Exception('Database (' . DB_NAME . ') does not exists!');
            }
        }
        else {
            throw new Exception('Could not connect to database with the provided credentials!');
        }
        $this->last_query = '';
    }
    
    /**
     * Method to close mysql connection
     * @return void
     */
    function __destruct() {
        mysql_close($this->db_conn);
    }
    
    function set_last_query($query) {
        $this->last_query = $query;
    }
    
    function last_query() {
        return $this->last_query;
    }
    
    /**
     * Method to run mysql database query
     * @return resource Query resource
     */
    function query($query, $return_query = FALSE) {
        /* if (defined('TIME_ZONE')) {
            $target_time_zone = new DateTimeZone(TIME_ZONE);
            $date_time = new DateTime('now', $target_time_zone);
            mysql_query('SET time_zone = "' . $date_time->format('P') . '"');
        } */
        
        if ($return_query === TRUE) {
            throw new Exception($query);
        }
        
        $result = mysql_query($query);
        $this->set_last_query($query);
        if (!empty(mysql_error($this->db_conn))) {
            throw new Exception('Invalid query: ' . mysql_error($this->db_conn) . ' in "' . $query . '"');
        }
        
        return $result;
    }
    
    /**
     * Method to get last inserted id
     * @return int last inserted primary key
     */
    function last_inserted_id() {
        return mysql_insert_id($this->db_conn);
    }
    
    function get_columns($data) {
        return implode(',', array_keys($data));
    }
    
    function get_column_values($data) {
        $values_str = '';
        $values = array_values($data);
        foreach($values as $key => $value) {
            $values_str .= ($key != 0) ? ', ' : '';
            if (is_string($value)) {
                $values_str .= '\'' . addcslashes(trim($value), "'") . '\'';
            }
            else if (is_numeric($value)) {
                $values_str .= $value;
            }
            else {
                $values_str .= '\'\'';
            }
        }        
        return $values_str;
    }
    
    function get_update_str($data) {
        $update_str = '';
        $i = 0;
        foreach($data as $key => $value) {
            $update_str .= ($i != 0) ? ', ' : '';
            $update_str .= trim($key) . ' = ';
            if (is_string($value)) {
                $update_str .= '\'' . addcslashes(trim($value), "'") . '\'';
            }
            else if (is_numeric($value)) {
                $update_str .= $value;
            }
            else {
                $update_str .= '\'\'';
            }
            
            $i ++;
        }
        
        return $update_str;
    }
    
    function get_where_str($where) {
        if (!is_array($where)) 
            return $where;
        
        $where = array_filter($where);
        $where_str = '';
        $i = 0;
        foreach($where as $key => $value) {
            $where_str .= ($i != 0) ? ' AND ' : '';
            $where_str .= trim($key) . ' = ';
            if (is_string($value)) {
                $where_str .= '\'' . addcslashes(trim($value), "'") . '\'';
            }
            else if (is_numeric($value)) {
                $where_str .= $value;
            }
            
            $i ++;
        }
        
        return $where_str;
    }
    
    /**
     * Method to insert row into table
     * $param1 $query string Mysql query
     * @return int Affected rows
     */
    function insert($tbl_name, $data) {
        if (empty($data))
            throw new Exception('Insert data should not be empty!');
        else if (empty($tbl_name))
            throw new Exception('Table name should not be empty!');
        
        $columns = $this->get_columns($data);
        $values = $this->get_column_values($data);
        $query = 'INSERT INTO ' . $tbl_name . ' (' . $columns . ') VALUES (' . $values . ')';
        $this->query($query);
        return $this->last_inserted_id();
    }
    
    
    function insert_batch($tbl_name, $data) {
        if (empty($data))
            throw new Exception('Insert data should not be empty!');
        else if (empty($tbl_name))
            throw new Exception('Table name should not be empty!');
        
        $columns = $this->get_columns($data);
        $values = $this->get_column_values($data);
        $query = 'INSERT INTO ' . $tbl_name . ' (' . $columns . ') VALUES (' . $values . '),(' . $values . ')';
        $this->query($query);
        return $this->last_inserted_id();
    }
    
    /**
     * Method to insert row into table
     * $param1 $query string Mysql query
     * @return int Affected rows
     */
    function update($tbl_name, $data, $where) {
        if (empty($data))
            throw new Exception('Update data should not be empty!');
        else if (empty($tbl_name))
            throw new Exception('Table name should not be empty!');
        
        $update_str = $this->get_update_str($data);
        $where_str = $this->get_where_str($where);
        $query = 'UPDATE ' . $tbl_name . ' SET ' . $update_str . ' WHERE ' . $where_str;
        $resource = $this->query($query);
        return $this->affected_rows($resource);
    }
    
    /**
     * Method to delete row from table
     * $param1 $query string Mysql query
     * @return int Affected rows
     */
    function delete($query) {
        $resource = $this->query($query);
        return $this->affected_rows($resource);
    }
    
    /**
     * Method to count rows in a resource
     * $param1 $resource resource Mysql resource
     * @return int Resource rows count
     */
    function num_rows($resource) {
        return mysql_num_rows($resource);
    }
    
    /**
     * Method to get affected rows
     * @return int Affected rows count
     */
    function affected_rows() {
        return mysql_affected_rows($this->db_conn);
    }

    /**
     * Method to get all rows from resource
     * $param1 $resource resource Mysql resource
     * @return array object Associative array object of resource
     */
    function fetch_object_array($resource) {
        $result = array();
        if ($this->num_rows($resource) > 0) {
            while($row_obj = mysql_fetch_object($resource)) {
                array_push($result, $row_obj);
            }
        }

        return $result;
    }

    /**
     * Method to get single row from resource
     * $param1 $resource resource Mysql resource
     * @return array Associative array of resource
     */
    function fetch_object_row($resource) {
        $result = array();
        if ($this->num_rows($resource) > 0) {
            $result = mysql_fetch_object($resource);
        }
        return $result;
    }

    /**
     * Method to get all rows from resource
     * $param1 $resource resource Mysql resource
     * @return array Associative array of resource
     */
    function fetch_assoc_array($resource) {
        $result = array();
        if ($this->num_rows($resource) > 0) {
            while($row = mysql_fetch_assoc($resource)) {
                array_push($result, $row);
            }
        }

        return $result;
    }

    /**
     * Method to get single row from resource
     * $param1 $resource resource Mysql resource
     * @return array
     */
    function fetch_assoc_row($resource) {
        $result = array();
        if ($this->num_rows($resource) > 0) {
            $result = mysql_fetch_assoc($resource);
        }
        return $result;
    }
    
    public function get_array_result($query) {
       
        $resources = $this->query($query);
        if ($this->num_rows($resources) > 0) :
            $result = $this->fetch_assoc_array($resources);
        else:
            $result = array();
        endif;
        
        return $result;
    }
    
    
    public function get_results($query) {
        
        $resources = $this->query($query);
        if ($this->num_rows($resources) > 0) :
            /**
             * Success.
             */
            $results['header'] = array(
                'status' => 'S',
                'message' => ucfirst( CMD ) . ' is executed successfully',
            );

            // Set Results
            $results['data'] = $this->fetch_assoc_array($resources);
        else:
            /**
             * Error
             */
            $results['header'] = array(
                'status' => 'N',
                'message' => 'No records found.',
            );
        endif;

        return $results;
    }
    
    function get($tbl_name, $args = array()) {
        
        // Nothing...
        
        return "This is my results";
        
    }
}
?>
