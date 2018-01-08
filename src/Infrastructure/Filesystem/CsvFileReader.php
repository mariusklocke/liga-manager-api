<?php

namespace HexagonalDream\Infrastructure\Filesystem;

use InvalidArgumentException;
use Iterator;

class CsvFileReader implements Iterator
{
    /** @var int */
    private $rowCounter;

    /** @var resource */
    private $fileHandle;

    /** @var array */
    private $currentRow;

    /**
     * @param string $filePath
     * @throws InvalidArgumentException If file could not be read
     */
    public function __construct(string $filePath)
    {
        $file = fopen($filePath, 'r');
        if (!is_resource($file)) {
            if (!file_exists($filePath)) {
                throw new InvalidArgumentException(sprintf('File "%s" does not exist', $filePath));
            }
            throw new InvalidArgumentException(sprintf('Cannot read from file "%s"', $filePath));
        }

        $this->rowCounter = 0;
        $this->fileHandle = $file;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return array
     */
    public function current()
    {
        return $this->currentRow;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->currentRow = fgetcsv($this->fileHandle);
        $this->rowCounter++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int
     */
    public function key()
    {
        return $this->rowCounter;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return is_array($this->currentRow);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        rewind($this->fileHandle);
        $this->currentRow = fgetcsv($this->fileHandle);
        $this->rowCounter = 0;
    }

    public function __destruct()
    {
        fclose($this->fileHandle);
    }
}
