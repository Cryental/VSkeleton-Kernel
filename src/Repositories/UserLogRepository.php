<?php

namespace Volistx\FrameworkKernel\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Volistx\FrameworkKernel\Models\UserLog;

class UserLogRepository
{
    /**
     * Create a new user log entry.
     *
     * @param array $inputs [subscription_id, url, ip, method, user_agent]
     */
    public function Create(array $inputs): Model|Builder
    {
        return UserLog::query()->create([
            'subscription_id' => $inputs['subscription_id'],
            'url' => $inputs['url'],
            'ip' => $inputs['ip'],
            'method' => $inputs['method'],
            'user_agent' => $inputs['user_agent'],
        ]);
    }

    /**
     * Find a user log entry by its ID.
     */
    public function FindById(string $logId): ?Model
    {
        return UserLog::query()->where('id', $logId)->first();
    }

    /**
     * Find all user log entries with pagination support.
     */
    public function FindAll(string $search, int $page, int $limit): ?LengthAwarePaginator
    {
        // Handle empty search
        if ($search === '') {
            $search = 'id:';
        }

        if (!str_contains($search, ':')) {
            return null;
        }

        $columns = Schema::getColumnListing('user_logs');
        $values = explode(':', $search, 2);
        $columnName = strtolower(trim($values[0]));

        if (!in_array($columnName, $columns)) {
            return null;
        }

        $searchValue = strtolower(trim($values[1]));

        return UserLog::query()
            ->where($values[0], 'LIKE', "%$searchValue%")
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Find all user log entries for a specific subscription with pagination support.
     */
    public function FindSubscriptionLogs(string $userId, string $subscriptionId, string $search, int $page, int $limit): ?LengthAwarePaginator
    {
        // Handle empty search
        if ($search === '') {
            $search = 'id:';
        }

        if (!str_contains($search, ':')) {
            return null;
        }

        $columns = Schema::getColumnListing('user_logs');
        $values = explode(':', $search, 2);
        $columnName = strtolower(trim($values[0]));

        if (!in_array($columnName, $columns)) {
            return null;
        }

        $searchValue = strtolower(trim($values[1]));

        return UserLog::query()
            ->where('user_logs.subscription_id', $subscriptionId)
            ->join('subscriptions', 'subscriptions.id', '=', 'user_logs.subscription_id')
            ->where('subscriptions.user_id', $userId)
            ->select('user_logs.*')
            ->where("user_logs.$values[0]", 'LIKE', "%$searchValue%")
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get the count of user log entries for a specific subscription within a given period.
     */
    public function FindSubscriptionLogsCountInPeriod(string $userId, string $subscriptionId, string $startDate, ?string $endDate): int
    {
        $query = UserLog::query()
            ->where('user_logs.subscription_id', $subscriptionId)
            ->join('subscriptions', 'subscriptions.id', '=', 'user_logs.subscription_id')
            ->where('subscriptions.user_id', $userId)
            ->select('user_logs.*')
            ->whereDate('user_logs.created_at', '>=', $startDate);

        if ($endDate) {
            $query = $query->whereDate('user_logs.created_at', '<=', $endDate);
        }

        return $query->count();
    }

    /**
     * Get all user log entries for a specific subscription grouped by date.
     */
    public function FindSubscriptionUsages(string $userId, string $subscriptionId): ?object
    {
        return UserLog::query()
            ->where('user_logs.subscription_id', $subscriptionId)
            ->join('subscriptions', 'subscriptions.id', '=', 'user_logs.subscription_id')
            ->where('subscriptions.user_id', $userId)
            ->select('user_logs.*')
            ->get()
            ->groupBy(function ($log) {
                return Carbon::parse($log->created_at)->format('d F Y'); // grouping by days
            });
    }
}
