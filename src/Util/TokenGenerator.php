<?php

namespace FrankProjects\UltimateWarfare\Util;

use Exception;

final class TokenGenerator
{
    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    public function generateToken(int $length): string
    {
        // random_bytes() is cryptographically secure
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
