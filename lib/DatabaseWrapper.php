<?php

require_once 'medoo.php';

$GLOBALS['THRIFT_ROOT'] = dirname(__FILE__) . '/php-thrift-hive-client';
require_once $GLOBALS['THRIFT_ROOT'] . '/packages/hive_service/ThriftHive.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php';
require_once 'ThriftHiveClientEx.php';
require_once 'Mapr.php';
require_once 'Vertica.php';

/**
 * class DatabaseWrapper
 *
 * Provides a unified access to SQL/Hive database
 *
 * @author Shlomi Noach <snoach@outbrain.com>
 * @license Apache 2.0 license.  See LICENSE document for more info
 * @created 2013-10-25
 */
class DatabaseWrapper {

    private $sql_database;
    private $hive_database;
    private $mapr_database;
    private $vertica_database;

    /**
     * Constructor.  Initialize the model object
     *
     * @param array $conf   The global config information
     */
    function __construct($conf) {
    	$this->sql_database = null;
    	$this->hive_database = null;
        $this->mapr_database = null;
        $this->vertica_database = null;
    	if($conf['database_type'] == 'mysql') {
	    	$this->sql_database = new medoo(array(
	    			'database_type' => 'mysql',
	    			'database_name' => $conf['default_schema'],
	    			'server' => $conf['host'],
	    			'port' => $conf['port'],
	    			'username' => $conf['user'],
	    			'password' => $conf['password']
	    	));
        }
        if($conf['database_type'] == 'hive') {
			$transport = new TSocket($conf['host'], $conf['port']);
			$transport->setSendTimeout(600 * 1000);
			$transport->setRecvTimeout(600 * 1000);
			$this->hive_database = new ThriftHiveClientEx(new TBinaryProtocol($transport));
        }
        if($conf['database_type'] == 'mapr') {
            $this->mapr_database = new Mapr($conf['host'], $conf['port'], $conf['user'], $conf['password'], $conf['default_schema']);
        }
        if($conf['database_type'] == 'vertica') {
            $this->vertica_database = new Vertica($conf['host'], $conf['port'], $conf['user'], $conf['password'], $conf['default_schema']);
        }
    }

    public function execute($query) {
    	$result = null;
    	if ($this->sql_database) {
    		$result = $this->sql_database->query($query);
    		$error = $this->sql_database->error();
    		if ($error) {
    			// $error[2] is the error message. If empty -- all is OK and there is no error.
	   			if (!empty($error[2]))
	    			throw new Exception($error[2]);
       		}
    	}
    	if ($this->hive_database) {
			$this->hive_database->open();
			$this->hive_database->execute($query);
			$result = $this->hive_database->fetchAll();
			$this->hive_database->close();
       	}
        if ($this->mapr_database) {
            $this->mapr_database->open();
            $this->mapr_database->execute($query);
	        $result = $this->mapr_database->fetchAll();
            $this->mapr_database->close();
        }
        if ($this->vertica_database) {
            $this->vertica_database->open();
            $this->vertica_database->execute($query);
            $result = $this->vertica_database->fetchAll();
            $this->vertica_database->close();
        }
       	return $result;
    }

    public function get_schemas_like($schema_wildcard) {
    	if ($this->sql_database) {
    	    $query = "SHOW DATABASES LIKE ".$this->sql_database->quote($schema_wildcard)."";
    		$result = $this->sql_database->query($query)->fetchAll();
    		$error = $this->sql_database->error();
    		if ($error) {
    			// $error[2] is the error message. If empty -- all is OK and there is no error.
	   			if (!empty($error[2]))
	    			throw new Exception($error[2]);
       		}
       		$result = array_map(function($schema) { return $schema[0]; }, $result);
       		return $result;
    	}
        throw new Exception("get_schemas_like() operation not supported");
    }

    public function get_error_message() {
    	if ($this->sql_database) {
    		$error = $this->sql_database->error();
    		if ($error) {
    			// $error[2] is the error message. If empty -- all is OK and there is no error.
    			return $error[2];
    		}
    		return null;
    	}
    	if ($this->hive_database) {
    	}
        if ($this->mapr_database) {
        }
        if ($this->vertica_database) {
        }
    }
}
//$database_wrapper = new DatabaseWrapper(array(
//    'database_type' => 'vertica',
//    'default_schema' => "",
//    'host' => "verticatest.chidc2.outbrain.com",
//    'port' => 5433,
//    'user' => "gabi",
//    'password' => "gabi@1234"
//));
//$database_wrapper->execute("SELECT table_name, table_type FROM all_tables
//         WHERE table_type = 'TABLE' ;")
?>
