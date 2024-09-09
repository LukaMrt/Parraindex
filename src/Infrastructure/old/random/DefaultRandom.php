<?php

namespace App\Infrastructure\old\random;

use App\Application\random\Random;
use Exception;

/**
 * Default implementation of Random interface. It uses PHP's built-in functions
 */
class DefaultRandom implements Random
{
    /**
     * @param int $length Length of the string
     * @return string Random string
     * @throws Exception If the length is less than 1
     */
    public function generate(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
