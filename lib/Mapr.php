<?php
/**
 * class Mapr
 *
 * Provides access to Mapr/Hive database
 *
 * @author Dovid Gefen <dovidgef>
 * @license Apache 2.0 license.  See LICENSE document for more info
 * @created 2017-08-09
 */


class Mapr
{
    private $host;
    private $port;
    private $user;
    private $password;
    private $conn;
    private $database;
    private $results_object;

    public function __construct($host, $port, $user, $pass, $database="") {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $pass;
        if ($database == "information_schema"){
            $database = "";
        }
        $this->database = $database;
    }

    /**
     * Open database connection
     * Update $conn property with database connection object
     * @return void
     */
    public function open() {
        if (!$this->conn) {
            $this->conn = odbc_connect("Driver={/opt/cloudera/hiveodbc/lib/64/libclouderahiveodbc64.so};Host={$this->host};Port={$this->port};Schema={$this->database};", 'mapr', 'mapr');
//            $this->conn = odbc_connect("Driver={Cloudera ODBC Driver for Apache Hive};Host={$this->host};Port={$this->port};Schema={$this->database};", $this->user, $this->password);
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
        set_error_handler("mapr_warning_handler", E_WARNING);
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

    public function get_results_object() {
        if ($this->results_object) {
            return $this->results_object;
        }
    }
}

class MaprExecuteException extends Exception {
}

// Function for changing error handler in order to convert warnings to Exceptions
function mapr_warning_handler($errno, $errstr) {
    throw new Exception($errstr, $errno);
}

//$database = new Mapr('127.0.0.1', '10000', 'mapr', 'mapr');
//$database->open();
//echo $database->get_conn();
//$database->execute("show tables");
//print_r($database->fetchAll());
//$database->close();
//echo $database->get_conn();