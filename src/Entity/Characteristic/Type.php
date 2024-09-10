<?php

namespace App\Entity\Characteristic;

enum Type: int
{
    case URL   = 0;
    case EMAIL = 1;
    case PHONE = 2;
}
