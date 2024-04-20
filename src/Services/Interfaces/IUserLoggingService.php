<?php

namespace Volistx\FrameworkKernel\Services\Interfaces;

interface IUserLoggingService
{
    /**
     * Create a new user log entry.
     */
    public function CreateUserLog(array $inputs): void;

    /**
     * Get a user log entry by log ID.
     */
    public function GetLog(string $logId): mixed;

    /**
     * Get all user log entries with pagination support.
     */
    public function GetLogs(string $search, int $page, int $limit): ?array;

    /**
     * Get all subscription log entries for a user and subscription with pagination support.
     */
    public function GetSubscriptionLogs(string $userId, string $subscriptionId, string $search, int $page, int $limit): array;

    /**
     * Get the count of subscription log entries for a user and subscription within the plan duration.
     */
    public function GetSubscriptionLogsCountInPlanDuration(string $userId, string $subscriptionId): int;

    /**
     * Get all user log entries for a specific subscription.
     */
    public function GetSubscriptionUsages(string $userId, string $subscriptionId): array;
}
