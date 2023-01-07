<?php

namespace App\application\random;

/**
 * Generate random numbers
 */
interface Random
{
    /**
     * Generate random number in range
     * @param int $length Length
     * @return string
     */
    public function generate(int $length): string;
}
