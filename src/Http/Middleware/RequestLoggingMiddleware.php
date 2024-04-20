<?php

namespace Volistx\FrameworkKernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Volistx\FrameworkKernel\Events\AdminRequestCompleted;
use Volistx\FrameworkKernel\Events\UserRequestCompleted;
use Volistx\FrameworkKernel\Facades\AccessTokens;
use Volistx\FrameworkKernel\Facades\PersonalTokens;
use Volistx\FrameworkKernel\Facades\Subscriptions;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Continue processing the request
        return $next($request);
    }

    /**
     * Perform actions after the response has been sent.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Check if a personal token is present and logging is enabled
        if (PersonalTokens::getToken() && PersonalTokens::getToken()->hidden === false) {
            if (PersonalTokens::getToken()->disable_logging === false) {
                $inputs = [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_id' => Subscriptions::getSubscription()?->user_id,
                    'user_agent' => $request->userAgent() ?? null,
                    'subscription_id' => Subscriptions::getSubscription()?->id,
                ];

                // Dispatch UserRequestCompleted event
                Event::dispatch(new UserRequestCompleted($inputs));
            }
        } // If an access token is present, log the admin request
        elseif (AccessTokens::getToken()) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent() ?? null,
                'access_token_id' => AccessTokens::getToken()?->id,
            ];

            // Dispatch AdminRequestCompleted event
            Event::dispatch(new AdminRequestCompleted($inputs));
        }
    }
}
