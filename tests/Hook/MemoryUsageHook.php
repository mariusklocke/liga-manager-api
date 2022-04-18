<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Hook;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

class MemoryUsageHook implements BeforeTestHook, AfterTestHook, AfterLastTestHook
{
    /** @var array */
    private array $measurements;

    public function __construct()
    {
        $this->measurements = [];
    }

    public function executeBeforeTest(string $test): void
    {
        $this->measurements[$test] = [
            'before' => memory_get_usage(true)
        ];
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this->measurements[$test]['after'] = memory_get_usage(true);
    }

    public function executeAfterLastTest(): void
    {
        $results = [];

        foreach ($this->measurements as $test => $measurement) {
            $results[] = [
                'test' => $test,
                'usage' => $measurement['after'] - $measurement['before']
            ];
        }

        usort($results, function (array $a, array $b) {
            return $b['usage'] <=> $a['usage'];
        });

        $results = array_slice($results, 0, 10);

        print PHP_EOL;
        print 'Tests with highest memory usage:';
        print PHP_EOL;

        foreach ($results as $result) {
            print str_pad($this->bytesToString($result['usage']), 12);
            print $this->stripNamespace($result['test']);
            print PHP_EOL;
        }
    }

    private function bytesToString(int $bytes): string
    {
        return sprintf('%.1f MiB', $bytes / 1024 / 1024);
    }

    /**
     * Strips the namespace part of a fully qualified class name
     *
     * @param string $className
     * @return string
     */
    private function stripNamespace(string $className): string
    {
        $pos = strrpos($className, '\\');

        return false !== $pos ? substr($className, $pos + 1) : $className;
    }
}
