<?php
/*
 * This file is part of the CubicMushroom/CsvIterator package.
 *
 * (c) Toby Griffiths <toby@cubicmushroom.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CubicMushroom\FileHandlers;

require __DIR__ . "/../../../../vendor/autoload.php";

class CsvIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testTest()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * Test to confirm that a constructor with no arguments throws an exception
     */
    public function testEmptyConstructor()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $iterator = new CsvIterator();
    }

    /**
     * Test constrictor with non-existing filename
     */
    public function testNonExistingFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $iterator = new CsvIterator(__DIR__ . '/files/not_a_test_file.csv');
    }

    /**
     * Test for an invalid $headers value
     */
    public function testInvalidHeaderArgument()
    {
        $this->setExpectedException('InvalidArgumentException');
        $iterator = new CsvIterator(
            __DIR__ . '/files/test_file.csv',
            'not_allowed'
        );
    }

    /**
     * Test constructor with just existing file
     */
    public function testJustFilename()
    {
        // We need to used the reflection class to access the protected properties of
        // the iterator object
        $reflection_class = new \ReflectionClass(__NAMESPACE__ . "\CsvIterator");
        $file_prop = $reflection_class->getProperty('file');
        $file_prop->setAccessible(true);
        $delimiter_prop = $reflection_class->getProperty('delimiter');
        $delimiter_prop->setAccessible(true);
        $hasHeaders_prop = $reflection_class->getProperty('hasHeaders');
        $hasHeaders_prop->setAccessible(true);
        $headers_prop = $reflection_class->getProperty('headers');
        $headers_prop->setAccessible(true);
        $iteratorRow_prop = $reflection_class->getProperty('iteratorRow');
        $iteratorRow_prop->setAccessible(true);

        $iterator = new CsvIterator(__DIR__ . '/files/test_file.csv');

        // Assert: File path is correct
        $this->assertStringEndsWith(
            'files/test_file.csv', $file_prop->getValue($iterator)
        );

        // Assert: Does not have headers
        $this->assertEquals(false, $hasHeaders_prop->getValue($iterator));

        // Assert: Delimiter is correct
        $this->assertEquals(',', $delimiter_prop->getValue($iterator));

        // Assert: Headers are NULL
        $this->assertNull($headers_prop->getValue($iterator));

        // Assert: iteratorRow = 0
        $this->assertEquals(0, $iteratorRow_prop->getValue($iterator));

        // Read rows & assert values
        $expected_values = array(
            array('key' => '1', 'values' => array(
                "First name","Surname","","","DoB"
            )),
            array('key' => '2', 'values' => array(
                "Toby","Griffiths","Test","Test","12/10/1973"
            )),
            array('key' => '3', 'values' => array(
                "Jo","Bloggs","Test","","11/5/1081"
            )),
            array('key' => '4', 'values' => array(
                "Anthony","Fuller","","Test","18/1/1993"
            )),
        );
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 4 rows are returned
        $this->assertEquals(4, $row);

        // Repeat file read to check reset works OK
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 4 rows are returned
        $this->assertEquals(4, $row);
    }

    /**
     * Test constructor ignoring headers
     */
    public function testFilenameAndIgnoreHeaders()
    {
        // We need to used the reflection class to access the protected properties of
        // the iterator object
        $reflection_class = new \ReflectionClass(__NAMESPACE__ . "\CsvIterator");
        $file_prop = $reflection_class->getProperty('file');
        $file_prop->setAccessible(true);
        $delimiter_prop = $reflection_class->getProperty('delimiter');
        $delimiter_prop->setAccessible(true);
        $hasHeaders_prop = $reflection_class->getProperty('hasHeaders');
        $hasHeaders_prop->setAccessible(true);
        $headers_prop = $reflection_class->getProperty('headers');
        $headers_prop->setAccessible(true);
        $iteratorRow_prop = $reflection_class->getProperty('iteratorRow');
        $iteratorRow_prop->setAccessible(true);

        $iterator = new CsvIterator(
            __DIR__ . '/files/test_file.csv',
            'ignore'
        );

        // Assert: File path is correct
        $this->assertStringEndsWith(
            'files/test_file.csv', $file_prop->getValue($iterator)
        );

        // Assert: Does not have headers
        $this->assertEquals(true, $hasHeaders_prop->getValue($iterator));

        // Assert: Delimiter is correct
        $this->assertEquals(',', $delimiter_prop->getValue($iterator));

        // Assert: Headers are NULL
        $this->assertNull($headers_prop->getValue($iterator));

        // Assert: iteratorRow = 0
        $this->assertEquals(1, $iteratorRow_prop->getValue($iterator));

        // Read rows & assert values
        $expected_values = array(
            array('key' => '2', 'values' => array(
                "Toby","Griffiths","Test","Test","12/10/1973"
            )),
            array('key' => '3', 'values' => array(
                "Jo","Bloggs","Test","","11/5/1081"
            )),
            array('key' => '4', 'values' => array(
                "Anthony","Fuller","","Test","18/1/1993"
            )),
        );
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 3 rows are returned
        $this->assertEquals(3, $row);


        // Repeat file read to check reset works OK
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 4 rows are returned
        $this->assertEquals(3, $row);
    }

    /**
     * Test constructor using headers
     */
    public function testFilenameUsingHeaders()
    {
        // We need to used the reflection class to access the protected properties of
        // the iterator object
        $reflection_class = new \ReflectionClass(__NAMESPACE__ . "\CsvIterator");
        $file_prop = $reflection_class->getProperty('file');
        $file_prop->setAccessible(true);
        $delimiter_prop = $reflection_class->getProperty('delimiter');
        $delimiter_prop->setAccessible(true);
        $hasHeaders_prop = $reflection_class->getProperty('hasHeaders');
        $hasHeaders_prop->setAccessible(true);
        $headers_prop = $reflection_class->getProperty('headers');
        $headers_prop->setAccessible(true);
        $iteratorRow_prop = $reflection_class->getProperty('iteratorRow');
        $iteratorRow_prop->setAccessible(true);

        $iterator = new CsvIterator(
            __DIR__ . '/files/test_file.csv',
            'use'
        );

        // Assert: File path is correct
        $this->assertStringEndsWith(
            'files/test_file.csv', $file_prop->getValue($iterator)
        );

        // Assert: Does not have headers
        $this->assertEquals(true, $hasHeaders_prop->getValue($iterator));

        // Assert: Delimiter is correct
        $this->assertEquals(',', $delimiter_prop->getValue($iterator));

        // Assert: Headers are NULL
        $expected_headers = array("First name","Surname","3","4","DoB");
        $this->assertEquals($expected_headers, $headers_prop->getValue($iterator));

        // Assert: iteratorRow = 0
        $this->assertEquals(1, $iteratorRow_prop->getValue($iterator));

        // Read rows & assert values
        $expected_values = array(
            array('key' => '2', 'values' => array(
                "First name" => "Toby",
                "Surname" => "Griffiths",
                "3" => "Test",
                "4" => "Test",
                "DoB" => "12/10/1973",
            )),
            array('key' => '3', 'values' => array(
                "First name" => "Jo",
                "Surname" => "Bloggs",
                "3" => "Test",
                "4" => "",
                "DoB" => "11/5/1081",
            )),
            array('key' => '4', 'values' => array(
                "First name" => "Anthony",
                "Surname" => "Fuller",
                "3" => "",
                "4" => "Test",
                "DoB" => "18/1/1993",
            )),
        );
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 3 rows are returned
        $this->assertEquals(3, $row);


        // Repeat file read to check reset works OK
        $row = 0;
        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected_values[$row]['key'], $key);
            $this->assertEquals($expected_values[$row]['values'], $value);
            $row++;
        }

        // Check only 4 rows are returned
        $this->assertEquals(3, $row);
    }

    /**
     * Test to check hasHeaders changes work as expected
     */
    public function testIgnoredHeadersLater()
    {

        // We need to used the reflection class to access the protected properties of
        // the iterator object
        $reflection_class = new \ReflectionClass(__NAMESPACE__ . "\CsvIterator");
        $hasHeaders_prop = $reflection_class->getProperty('hasHeaders');
        $hasHeaders_prop->setAccessible(true);
        $headers_prop = $reflection_class->getProperty('headers');
        $headers_prop->setAccessible(true);

        $iterator = new CsvIterator(
            __DIR__ . '/files/test_file.csv',
            'none'
        );

        /**
         * Test ignoring headers
         */
        $iterator->setHasHeaders('ignore');

        // Verify the hasHeader property has been updated
        $this->assertEquals(true, $hasHeaders_prop->getValue($iterator));

        // Verify the headers are stored correctly

        // Verify that the 1st row returned is now actually the 2nd row
        foreach ($iterator as $key => $value) {
            $this->assertEquals(2, $key);
            $this->assertEquals(
                $value,
                array("Toby","Griffiths","Test","Test","12/10/1973")
            );
            break;
        }

        /**
         * Test using headers
         */
        $iterator->setHasHeaders('use');

        // Verify the hasHeader property has been updated
        $this->assertEquals(true, $hasHeaders_prop->getValue($iterator));

        // Verify the headers are stored correctly

        // Verify that the 1st row returned is now actually the 2nd row
        foreach ($iterator as $key => $value) {
            $this->assertEquals(2, $key);
            $this->assertEquals(
                $value,
                array(
                    "First name" => "Toby",
                    "Surname" => "Griffiths",
                    "3" => "Test",
                    "4" => "Test",
                    "DoB" => "12/10/1973"
                )
            );
            break;
        }
    }
}