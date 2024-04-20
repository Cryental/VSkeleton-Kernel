<?php

namespace Volistx\FrameworkKernel\Services\Interfaces;

interface IAdminLoggingService
{
    /**
     * Create a new admin log entry.
     */
    public function CreateAdminLog(array $inputs): void;

    /**
     * Get an admin log entry by log ID.
     */
    public function GetAdminLog(string $logId): mixed;

    /**
     * Get all admin log entries with pagination support.
     */
    public function GetAdminLogs(string $search, int $page, int $limit): ?array;
}
