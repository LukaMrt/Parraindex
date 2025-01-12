<?php

declare(strict_types=1);

namespace App\Entity\Characteristic;

enum Type: int
{
    case URL   = 0;
    case EMAIL = 1;
    case PHONE = 2;
}
