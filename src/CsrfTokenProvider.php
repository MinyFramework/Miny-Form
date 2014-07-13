<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\HTTP\Session;

class CsrfTokenProvider
{
    /**
     * @var Session
     */
    protected $session;
    protected $tokenGenerated = false;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @todo use a proper CSPRNG
     */
    public function generateToken()
    {
        if (!$this->tokenGenerated) {
            $this->tokenGenerated = true;
            $this->session->flash('csrf_token', sha1(mt_rand() . microtime()));
        }

        return $this->session->csrf_token;
    }

    public function matchToken($requestToken)
    {
        if (!isset($this->session->csrf_token)) {
            return false;
        }

        return $requestToken === $this->session->csrf_token;
    }
}
