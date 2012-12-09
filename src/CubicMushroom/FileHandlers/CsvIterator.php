<?php
/**
 * This file is part of the CubicMushroom/CsvIterator package.
 *
 * (c) Toby Griffiths <toby@cubicmushroom.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Code based on source found here...
 *   http://php.net/manual/en/function.fgetcsv.php#57802
 *
 * @package    Cubic_Mushroom_File_Handlers
 * @subpackage CSV_Handlers
 *
 * @author     Toby Griffiths <toby@cubicmushroom.com>
 * @copyright  2012 Cubic Mushroom Ltd
 * @license    See LICENSE file
 */

namespace CubicMushroom\FileHandlers;

// We need to enable support for Mac format line endings
ini_set("auto_detect_line_endings", true);

/**
 * Class used to iterate through a CSV file a row at a time
 *
 * @package    Cubic_Mushroom_File_Handlers
 * @subpackage CSV_Handlers
 *
 * @author     Toby Griffiths <toby@cubicmushroom.com>
 * @copyright  2012 Cubic Mushroom Ltd
 * @license    See LICENSE file
 */
class CsvIterator implements \Iterator
{
    /**
     * File path provided
     * @var string
     */
    protected $file;

    /**
     * File handle used for accessing file
     * @var resource
     */
    protected $fileHandle;

    /**
     * Delimited used when parsing CSV file
     * @var string
     */
    protected $delimiter;

    /**
     * Row size in characters (0 = no limit/all of row read)
     * @var integer
     */
    protected $rowSize;

    /**
     * Flag indicating whether the file had a header
     * @var boolean
     */
    protected $hasHeaders = false;

    /**
     * Whether to use headers to index returned row array
     * @var boolean
     */
    protected $useHeaders = false;

    /**
     * Array of headers to use (if requested)
     * @var array
     */
    protected $headers;

    /**
     * Stores custom headers, if specified
     * @var array
     */
    protected $customHeaders;

    /**
     * Current row stored in iteratorElement
     * @var integer
     */
    protected $iteratorRow;

    /**
     * Contents of the current row
     * @var array
     */
    protected $iteratorElement;

    /**
     * Currently does nothing
     *
     * @param string       $file      Full path to file
     * @param string|array $headers   See CsvIterator::setHeaders()
     * @param string       $delimiter String to use as delimiter when reading CSV
     *                                file row
     * @param integer      $rowSize   Number of bytes to read from each row.
     *                                Defaults to 0 (no limit)
     *
     * @uses CsvIterator::setHeaders()
     *
     * @return void
     */
    public function __construct(
        $file, $headers = 'none', $delimiter = ",", $rowSize = 0
    ) {
        if (!is_file($file)) {
            throw new \InvalidArgumentException(
                'File not found (' . $this->file . ')'
            );
        }
        if (! in_array($headers, array('none', 'use', 'ignore'))) {
            throw new \InvalidArgumentException(
                '$headers must be one of none|use|ignore'
            );
        }
        if (! is_integer($rowSize)) {
            throw new \InvalidArgumentException('$rowSize must be an integer');
        }

        $this->file = $file;

        $this->delimiter = $delimiter;

        $this->iteratorRow = 0;

        $this->setHeaders($headers);

        $this->openFile();
        $this->rowSize = $rowSize;
    }

    /**
     * Sets how to use the headers.
     *
     * @param string|array $headers none|use|ignore...
     *                              'none' = File does not include a header
     *                              'use'  = Header row values will be used as keys
     *                                       for returned data
     *                              'none' = File has a header row, but returned
     *                                       row will have numbered index
     *
     * @return void
     */
    public function setHeaders($headers)
    {
        $allowed_values = array('none', 'ignore', 'use');

        if (! in_array($headers, $allowed_values)) {
            throw new InvalidArgumentException(
                '$headers value must be one of "' . implode('", "', $allowed_values) 
                . '"'
            );
        }

        if ('none' == $headers) {
            $this->hasHeaders = false;
            $this->useHeaders = false;
        } else {
            $this->hasHeaders = true;
            if ('ignore' == $headers) {
                $this->useHeaders = false;
            } else {
                $this->useHeaders = true;
            }
        }

        // If headers are now needed, we need to re-open the file so that the headers
        // property is updated
        if ($this->hasHeaders) {
            @fclose($this->fileHandle);
            $this->openFile();
        }
    }

    /**
     * Allows used to specify custom headers to use
     *
     * @param array $headers Array of headers to use
     *
     * @return void
     */
    public function setCustomHeaders($headers = null)
    {
        if (empty($headers)) {
            $this->headers = null;
            $this->useHeaders = false;
        } elseif (is_array($headers)) {
            $this->customHeaders = $headers;
            $this->useHeaders = true;
        } else {
            throw new InvalidArgumentException(
                "Custom headers my be provided as an array"
            );
        }
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

                // Empty headers are now handled when reading the current row
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

        $headers = $this->headers;
        if (! empty($this->customHeaders)) {
            $headers = $this->customHeaders;
        }

        if (! empty($headers)) {
            // We need to use a temporary array, as previously updated existing array
            // & unset numeric value, but title-less columns (using numbers) would
            // get unset
            $newElement = array();
            foreach ($this->iteratorElement as $key => $value) {
                $newKey = $headers[$key];
                if (empty($newKey)) {
                    $newKey = "Column " . ($key + 1);
                }
                $newElement[$newKey] = $value;
            }
            $this->iteratorElement = $newElement;
        }

        $this->iteratorRow++;

        return $this->iteratorElement;
    }

    /**
     * Return the key of the current element
     *
     * @return integer Returns the current row of the CSV file to use as the iterator
     *         key
     */
    public function key()
    {
        return $this->iteratorRow;

    }

    /**
     * Move forward to next element
     *
     * @return boolean Returns true if we're not at the end of the file yet
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
        if (! $this->next()) {
            fclose($this->fileHandle);
            return false;
        }
        return true;
    }

}