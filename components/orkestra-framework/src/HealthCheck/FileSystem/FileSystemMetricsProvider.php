<?php

namespace Morebec\Orkestra\Framework\HealthCheck\FileSystem;

use RuntimeException;

/**
 * Default implementation of a {@link FileSystemMetricProviderInterface} that uses internal PHP
 * functions to return the data.
 */
class FileSystemMetricsProvider implements FileSystemMetricProviderInterface
{
    private string $fileSystemDirectory;

    public function __construct(string $fileSystemDirectory = __DIR__)
    {
        $this->fileSystemDirectory = $fileSystemDirectory;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalSpace(): int
    {
        $totalSpace = disk_total_space($this->fileSystemDirectory);
        if ($totalSpace === false) {
            throw $this->createFileSystemException();
        }

        return (int) $totalSpace;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsedSpace(): int
    {
        return $this->getTotalSpace() - $this->getFreeSpace();
    }

    /**
     * {@inheritDoc}
     */
    public function getUsedSpaceAsPercentage(): float
    {
        return round($this->getUsedSpace() / $this->getTotalSpace(), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function getFreeSpace(): int
    {
        $freeSpace = disk_free_space($this->fileSystemDirectory);
        if ($freeSpace === false) {
            throw $this->createFileSystemException();
        }

        return (int) $freeSpace;
    }

    public function getFreeSpaceAsPercentage(): float
    {
        return round($this->getFreeSpace() / $this->getTotalSpace(), 4);
    }

    protected function createFileSystemException(): RuntimeException
    {
        return new RuntimeException('Could not calculate space of file system for directory '.$this->fileSystemDirectory);
    }
}
