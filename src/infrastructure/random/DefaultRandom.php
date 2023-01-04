<?php

namespace App\infrastructure\random;

use App\application\random\Random;

/**
 * Genaerate random string
 */
class DefaultRandom implements Random
{
    /**
     * Generate a random string
     * @param int $length Length of the string
     * @return string
     */
    public function generate(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
