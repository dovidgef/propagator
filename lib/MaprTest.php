<?php
/**
* class MaprTest
*
* Tests Mapr class
*
* @author Dovid Gefen <dovidgef>
* @license Apache 2.0 license.  See LICENSE document for more info
* @created 2017-08-09
*/

require_once("Mapr.php");


class MaprTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Test Mapr object creation
//        $this->testMapr = new Mapr("127.0.0.1", "10000", "mapr", "mapr");
        $this->testMapr = new Mapr("10.2.3.124", "10000", "mapr", "mapr");
    }

    function testDatabaseConnection(){
        $this->testMapr->open();
        // Test if conn attribute contains resource
        $this->assertInternalType("resource", $this->testMapr->get_conn());

        $this->testMapr->close();
        // Test if conn attribute contains null
        $this->assertInternalType("null", $this->testMapr->get_conn());
    }

    function testExecuteBasic(){
        $this->testMapr->open();
        $this->testMapr->execute("SHOW DATABASES");
        $this->assertInternalType("resource", $this->testMapr->get_results_object());
        // Check if results contain Mapr/Hive default database
        $this->assertEquals("default", $this->testMapr->fetchAll()[0]["database_name"]);
        $this->testMapr->close();
    }

    function testExecuteAdvanced(){
        $this->testMapr->open();
        $this->testMapr->execute("CREATE TABLE students (name VARCHAR(64), age INT, gpa DECIMAL(3, 2))");
        $this->testMapr->execute("INSERT INTO TABLE students VALUES ('fred flintstone', 35, 1.28), ('barney rubble', 32, 2.32)");
        $this->assertInternalType("resource", $this->testMapr->get_results_object());

        $this->testMapr->execute("SELECT * FROM students");
        $this->assertCount(2, $this->testMapr->fetchAll());
        $this->testMapr->execute("DROP TABLE IF EXISTS students");
        $this->testMapr->close();
    }
}
