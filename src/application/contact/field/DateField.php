<?php

namespace App\application\contact\field;

use DateTime;

class DateField extends Field
{

    public function isValid(string $value): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date
            && $date->format('Y-m-d') === $value
            && DateTime::createFromFormat('Y-m-d', '2010-01-01') <= $date;
    }

}
