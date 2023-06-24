<?php

namespace Volistx\FrameworkKernel\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Volistx\FrameworkKernel\Facades\AccessTokens;
use Volistx\FrameworkKernel\Facades\Messages;
use Volistx\FrameworkKernel\Facades\Permissions;
use Volistx\FrameworkKernel\Services\Interfaces\IAdminLoggingService;

class AdminLogController extends Controller
{

    private IAdminLoggingService $adminLoggingService;

    public function __construct(IAdminLoggingService $adminLoggingService)
    {
        $this->module = 'admin-logs';
        $this->adminLoggingService = $adminLoggingService;
    }

    public function GetAdminLog(Request $request, $log_id): JsonResponse
    {
        try {
            if (!Permissions::check(AccessTokens::getToken(), $this->module, 'view')) {
                return response()->json(Messages::E401(), 401);
            }

            $validator = $this->GetModuleValidation($this->module)->generateGetValidation([
                'log_id' => $log_id,
            ]);

            if ($validator->fails()) {
                return response()->json(Messages::E400($validator->errors()->first()), 400);
            }

            $log = $this->adminLoggingService->GetAdminLog($log_id);

            if (!$log) {
                return response()->json(Messages::E404(), 404);
            }

            return response()->json($log);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetAdminLogs(Request $request): JsonResponse
    {
        try {
            if (!Permissions::check(AccessTokens::getToken(), $this->module, 'view-all')) {
                return response()->json(Messages::E401(), 401);
            }

            $search = $request->input('search', '');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 50);

            $validator = $this->GetModuleValidation($this->module)->generateGetAllValidation([
                'page' => $page,
                'limit' => $limit,
            ]);

            if ($validator->fails()) {
                return response()->json(Messages::E400($validator->errors()->first()), 400);
            }

            $logs = $this->adminLoggingService->GetAdminLogs($search, $page, $limit);

            if (!$logs) {
                return response()->json(Messages::E400(trans('volistx::invalid_search_column')), 400);
            }

            return response()->json($logs);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }
}
