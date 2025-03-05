<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Filesystem;

class Directory
{
    use PathTrait;

    /**
     * Clear contents of directory recursively
     */
    public function clear(): void
    {
        $this->assertTrue($this->exists(), "Cannot clear {$this->path}: Directory does not exist");
        $this->assertTrue($this->isWritable(), "Cannot clear {$this->path}: Directory is not writable");

        foreach ($this->list() as $item) {
            if ($item instanceof Directory) {
                $item->clear();
            }
            $item->delete();
        }
    }

    /**
     * Create directory
     */
    public function create(): void
    {
        $this->assertTrue(!$this->exists(), "Cannot create {$this->path}: File or directory already exists");

        mkdir($this->path);
    }

    /**
     * Delete directory (only works if empty)
     */
    public function delete(): void
    {
        if ($this->exists()) {
            $this->assertTrue($this->isEmpty(), "Cannot delete {$this->path}: Directory is not empty");
            $this->assertTrue($this->isWritable(), "Cannot delete {$this->path}: Directory is not writable");
            rmdir($this->path);
        }
    }

    /**
     * Returns if directory is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->list()) === 0;
    }

    /**
     * List contents
     * 
     * @return array<File|Directory>
     */
    public function list(): array
    {
        $this->assertTrue($this->exists(), "Cannot list {$this->path}: Directory does not exist");
        $this->assertTrue($this->isReadable(), "Cannot list {$this->path}: Directory is not readable");

        $items = [];
        $handle = opendir($this->path);
        while (false !== ($item = readdir($handle))) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $this->path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $items[] = new Directory($path);
            } else {
                $items[] = new File($path);
            }
        }
        closedir($handle);

        return $items;
    }

    public static function getTemp(): Directory
    {
        return new Directory(sys_get_temp_dir());
    }
}