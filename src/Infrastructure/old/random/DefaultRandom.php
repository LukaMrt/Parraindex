<?php

declare(strict_types=1);

namespace App\Infrastructure\old\random;

use App\Application\random\Random;
use Random\RandomException;

class DefaultRandom implements Random
{
    /**
     * @throws RandomException
     */
    #[\Override]
    public function generate(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
