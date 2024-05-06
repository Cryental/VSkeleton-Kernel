<?php

namespace Volistx\FrameworkKernel\Helpers;

class AccessTokensCenter
{
    private mixed $token = null;

    /**
     * Get the access token.
     *
     * @return mixed The access token
     */
    public function getToken(): mixed
    {
        return $this->token;
    }

    /**
     * Set the access token.
     *
     * @param mixed $token The access token
     */
    public function setToken(mixed $token): void
    {
        $this->token = $token;
    }
}
