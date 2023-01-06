<?php

namespace App\model\person\characteristic;


use ValueError;

/**
 * Types of characteristics
 */
enum CharacteristicType: string
{
    case URL = '';
    case PHONE = 'tel:';
    case EMAIL = 'mailto:';


    /**
     * @param string $name Name of the characteristic type
     * @return CharacteristicType Characteristic type
     * @throws ValueError If the characteristic type does not exist
     */
    public static function fromName(string $name): CharacteristicType
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status;
            }
        }
        throw new ValueError("$name is not a valid backing value for enum " . self::class);
    }


    /**
     * @return string Prefix of the characteristic type
     */
    public function getPrefix(): string
    {
        return $this->value;
    }
}
