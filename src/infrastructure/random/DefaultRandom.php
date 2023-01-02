<?php

namespace App\infrastructure\random;

use App\application\random\Random;

class DefaultRandom implements Random
{
    public function generate(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
