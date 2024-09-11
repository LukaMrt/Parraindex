<?php

namespace App\Application\random;

interface Random
{
    public function generate(int $length): string;
}
