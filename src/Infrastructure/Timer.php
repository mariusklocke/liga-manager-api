<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use LogicException;

class Timer
{
    /** @var float|null Timestamp in seconds */
    private $startTime = null;

    /**
     * Start the timer
     */
    public function start(): void
    {
        $this->startTime = $this->getTimestamp();
    }

    /**
     * Stop the timer
     *
     * @return int elapsed time in milliseconds
     */
    public function stop(): int
    {
        if ($this->startTime === null) {
            throw new LogicException('Timer was stopped, but never started');
        }

        $elapsed = $this->getTimestamp() - $this->startTime;

        $this->startTime = null;

        return (int)($elapsed * 1000);
    }

    /**
     * @return float
     */
    private function getTimestamp(): float
    {
        return microtime(true);
    }
}
