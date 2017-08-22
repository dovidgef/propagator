<?php
/**
 * class VertiaTest
 *
 * Tests Vertica class
 *
 * @author Dovid Gefen <dovidgef>
 * @license Apache 2.0 license.  See LICENSE document for more info
 * @created 2017-08-17
 */

require_once("Vertica.php");


class VerticaTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Test Vertica object creation
        $this->testVertica = new Vertica("127.0.0.1", "5433", "dbadmin", "password");
    }

    function testDatabaseConnection(){
        $this->testVertica->open();
        // Test if conn attribute contains resource
        $this->assertInternalType("resource", $this->testVertica->get_conn());

        $this->testVertica->close();
        // Test if conn attribute contains null
        $this->assertInternalType("null", $this->testVertica->get_conn());
    }

    function testExecuteBasic(){
        $this->testVertica->open();
        $this->testVertica->execute("SELECT table_schema FROM v_catalog.tables");
        $this->assertInternalType("resource", $this->testVertica->get_results_object());
        // Check if results contain Mapr/Hive default database
        $this->assertEquals("outbrain", $this->testVertica->fetchAll()[0]["table_schema"]);
        $this->testVertica->close();
    }

    function testExecuteAdvanced(){
        $this->testVertica->open();
        $this->testVertica->execute("CREATE TABLE students (name VARCHAR(64), age INT, gpa DECIMAL(3, 2))");
        $this->testVertica->execute("INSERT INTO students VALUES ('fred flintstone', 35, 1.28)");
        $this->assertInternalType("resource", $this->testVertica->get_results_object());
        $this->testVertica->execute("INSERT INTO students VALUES ('barney rubble', 32, 2.32)");
        $this->assertInternalType("resource", $this->testVertica->get_results_object());

        $this->testVertica->execute("SELECT * FROM students");
        $this->assertCount(2, $this->testVertica->fetchAll());
        $this->testVertica->execute("DROP TABLE IF EXISTS students");
        $this->testVertica->close();
    }
}
