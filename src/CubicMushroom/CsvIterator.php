<?php
/*
 * This file is part of the CubicMushroom/CsvIterator package.
 *
 * (c) Toby Griffiths <toby@cubicmushroom.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Code based on source found here...
 *   http://snipplr.com/view/1986/
 */

namespace CubicMushroom;

// We need to enable support for Mac format line endings
ini_set("auto_detect_line_endings", true);

class CsvIterator implements \Iterator
{
    /**
     * File path provided
     */
    protected $file;

    /**
     * File handle used for accessing file
     */
    protected $fileHandle;

    /**
     * Delimited used when parsing CSV file
     */
    protected $delimiter;

    /**
     * Row size in characters (0 = no limit/all of row read)
     */
    protected $rowSize;

    /**
     * Flag indicating whether the file had a header
     */
    protected $hasHeaders = false;

    /**
     * Whether to use headers to index returned row array
     */
    protected $useHeaders = false;

    /**
     * Array of headers to use (if requested)
     */
    protected $headers;

    /**
     * Current row stored in iteratorElement
     */
    protected $iteratorRow;

    /**
     * Contents of the current row
     */
    protected $iteratorElement;

    /**
     * Currently does nothing
     *
     * @param string $file      Full path to file
     * @param string $delimiter String to use as delimiter when reading CSV file row
     * @param string $header    none|use|ignore...
     *                          'none' = File does not include a header
     *                          'use'  = Header row values will be used as keys for
     *                                   returned data
     *                          'none' = File has a header row, but returned row will
     *                                   have numbered index
     */
    public function __construct($file, $header = 'none', $delimiter = ",", $rowSize = 0)
    {
        if (!is_file($file)){
            throw new \InvalidArgumentException(
                'File not found (' . $this->file . ')'
            );
        }
        if (! in_array($header, array('none', 'use', 'ignore'))) {
            throw new \InvalidArgumentException(
                '$header must be one of none|use|ignore'
            );
        }
        if (! is_integer($rowSize)) {
            throw new \InvalidArgumentException('$rowSize must be an integer');
        }

        $this->file = $file;

        $this->delimiter = $delimiter;

        $this->iteratorRow = 0;

        if ('none' != $header) {
            $this->hasHeaders = true;
            if ('use' == $header) {
                $this->useHeaders = true;
            }
        }

        $this->openFile();
        $this->rowSize = $rowSize;
    }

    /**
     * Method to open file
     *
     * Stores file handle in $this->fileHandle
     *
     * @return void
     */
    protected function openFile()
    {
        $this->fileHandle = fopen($this->file, "r");

        if ($this->fileHandle === false) {
            throw new \RuntimeException(
                'Unable to open file for reading (' . $this->file . ')'
            );
        }

        if (! empty($this->hasHeaders)) {
            $header_values = $this->current();
            if (! empty($this->useHeaders)) {
                $this->headers = array_values($header_values);
                foreach ($this->headers as $key => $value) {
                    if (empty($value)) {
                        $this->headers[$key] = $key+1;
                    }
                }
            }
        }
    }

    /**
     * Return the current element
     *
     * @return array
     */
    public function current()
    {

        $this->iteratorElement = fgetcsv(
            $this->fileHandle, $this->rowSize, $this->delimiter
        );

        if (! empty($this->headers)) {
            // We need to use a temporary array, as previously updated existing array
            // & unset numeric value, but title-less columns (using numbers) would
            // get unset
            $newElement = array();
            foreach ($this->iteratorElement as $key => $value) {
                $newElement[$this->headers[$key]] = $value;
            }
            $this->iteratorElement = $newElement;
        }

        $this->iteratorRow++;

        return $this->iteratorElement;
    }

    /**
     * Return the key of the current element
     */
    public function key()
    {
        return $this->iteratorRow;

    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        return ! @feof($this->fileHandle);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {

        $fileHandleResource = @get_resource_type($this->fileHandle);
        if (empty($fileHandleResource) || 'Unknown' == $fileHandleResource) {
            $this->openFile();
        }

        $this->iteratorRow = 0;
        @rewind($this->fileHandle);

        // If we have headers read the headers, so we skip them
        if (! empty($this->hasHeaders)) {
            $this->current();
        }
    }

    /**
     * Checks if current position is valid
     *
     * @return bool Returns true if there is something at the current pointer, false
     *              if not
     */
    public function valid()
    {
        if(! $this->next())
        {
            fclose($this->fileHandle);
            return false;
        }
        return true;
    }

}