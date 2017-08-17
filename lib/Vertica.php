<?php
/**
 * class Vertica
 *
 * Provides access to Vertica database
 *
 * @author Dovid Gefen <dovidgef>
 * @license Apache 2.0 license.  See LICENSE document for more info
 * @created 2017-08-10
 */

class Vertica
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
//            $this->conn = odbc_connect("Driver={Vertica};Host={$this->host};Port={$this->port};Database={$this->database};", $this->user, $this->password);
            $this->conn = odbc_connect("Driver={/opt/vertica/lib64/libverticaodbc.so};Host={$this->host};Port={$this->port};Database={$this->database};", $this->user, $this->password);
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
        set_error_handler("vertica_warning_handler", E_WARNING);
        $queries = preg_split('/;/', $str);

        foreach ($queries as $query) {
            $query = str_replace(array('\r\n', '\n', '\r'),  ' ', $query);
            $query = ltrim($query);
            if ($query == '') return false;

            try {
                $this->results_object = odbc_exec($this->conn, $query);
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $msg = "VerticaExecuteException: Execute Error:: $msg  query:: $query ";
                throw new VerticaExecuteException($msg);
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

class VerticaExecuteException extends Exception {
}

// Function for changing error handler in order to convert warnings to Exceptions
function vertica_warning_handler($errno, $errstr) {
    throw new Exception($errstr, $errno);
}


//$database = new Vertica('verticatest.chidc2.outbrain.com', '5433', 'gabi', 'gabi@1234');
////$database = new Vertica('localhost', '5433', 'dbadmin', 'password');
//$database->open();
//echo $database->get_conn();
//$database->execute("CREATE TABLE Product_Dimension2 (
//       Product_Key                    integer NOT NULL,
//       Product_Description            varchar(128),
//       SKU_Number                     char(32) NOT NULL,
//       Category_Description           char(32),
//       Department_Description         char(32) NOT NULL,
//       Package_Type_Description       char(32),
//       Package_Size                   char(32),
//       Fat_Content                    integer,
//       Diet_Type                      char(32),
//       Weight                         integer,
//       Weight_Units_of_Measure        char(32),
//       Shelf_Width                    integer,
//       Shelf_Height                   integer,
//       Shelf_Depth                    integer
//)
//;");
//print_r($database->fetchAll());
//$database->close();
//echo $database->get_conn();