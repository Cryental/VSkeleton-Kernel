<?php

namespace Volistx\FrameworkKernel\AuthValidationRules\Admins;

use Volistx\FrameworkKernel\AuthValidationRules\Users\ValidationRuleBase;
use Volistx\FrameworkKernel\Enums\AccessRule;
use Volistx\FrameworkKernel\Facades\AccessTokens;
use Volistx\FrameworkKernel\Facades\Messages;
use Wikimedia\IPSet;

class IPValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = AccessTokens::getToken();

        if ($token->ip_rule === AccessRule::NONE) {
            return true;
        }

        $ipSet = new IPSet($token->ip_range);

        if ($token->ip_rule === AccessRule::BLACKLIST && $ipSet->match($this->request->getClientIp()) ||
            ($token->ip_rule === AccessRule::WHITELIST && !$ipSet->match($this->request->getClientIp()))) {
            return [
                'message' => Messages::E403(trans('volistx::service.not_allowed_to_access_from_your_ip')),
                'code'    => 403,
            ];
        }

        return true;
    }
}