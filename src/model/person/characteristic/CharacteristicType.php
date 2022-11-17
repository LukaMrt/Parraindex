<?php

namespace App\model\person\characteristic;

enum CharacteristicType : string {

	case URL = '';
	case PHONE = 'tel:';
	case ADDRESS = 'https://www.google.fr/maps/place/';
	case EMAIL = 'mailto:';


	public static function fromName(string $name): CharacteristicType {
		foreach (self::cases() as $status) {
			if( $name === $status->name ){
				return $status;
			}
		}
		throw new \ValueError("$name is not a valid backing value for enum " . self::class );
	}

	public function getPrefix(): string {
		return $this->value;
	}
}
