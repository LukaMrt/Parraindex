<?php

namespace App\Infrastructure\old\random;

use App\Application\random\Random;
use Random\RandomException;

class DefaultRandom implements Random
{
    /**
     * @throws RandomException
     */
    public function generate(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
