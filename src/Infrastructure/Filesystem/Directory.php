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
        mkdir($this->path);
    }

    /**
     * Delete directory (only works if empty)
     */
    public function delete(): void
    {
        rmdir($this->path);
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