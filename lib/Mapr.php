<?php
/**
 * Created by PhpStorm.
 * User: dovidgef
 * Date: 8/9/17
 * Time: 2:09 PM
 */


class Mapr
{
    private $host;
    private $port;
    private $conn;
    private $results_object;

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Open database connection
     * Update $conn property with database connection object
     * @return void
     */
    public function open() {
        if (!$this->conn) {
            $this->conn = odbc_connect("Driver={/opt/cloudera/hiveodbc/lib/64/libclouderahiveodbc64.so};Host={$this->host};Port={$this->port};", 'mapr', 'mapr');
        }
    }

    /**
     * Close database connection
     * Set $conn property to NULL
     * @return void
     */
    public function close() {
        if ($this->conn) {
            odbc_close($this->conn);
            $this->conn = NULL;
        }
    }

    /**
     * Execute query
     * @param $str
     * @return bool
     * @throws MaprExecuteException
     */
    public function execute($str) {
        // ex) use my_db; select * from my_db
        set_error_handler("warning_handler", E_WARNING);
        $queries = preg_split('/;/', $str);

        foreach ($queries as $query) {
            $query = str_replace(array('\r\n', '\n', '\r'),  ' ', $query);
            $query = ltrim($query);
            if ($query == '') return false;

            try {
                $this->results_object = odbc_exec($this->conn, $query);
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $msg = "MaprExecuteException: Execute Error:: $msg  query:: $query ";
                throw new MaprExecuteException($msg);
            }
        }
        restore_error_handler();
    }

    /**
     * Fetch all results
     * Return array of arrays
     * @return array
     */
    public function fetchAll() {
        $results_array = array();
        while($row = odbc_fetch_array($this->results_object)){
            $results_array[] = $row;
        }
        return $results_array;
    }

    public function get_conn() {
        if ($this->conn) {
            return $this->conn;
        }
    }
}

class MaprExecuteException extends Exception {
}

function warning_handler($errno, $errstr) {
    throw new Exception($errstr, $errno);
}

//$database = new Mapr('127.0.0.1', '10000');
//$database->open();
//echo $database->get_conn();
//$database->execute("show databases");
//print_r($database->fetchAll());
//$database->close();
//echo $database->get_conn();