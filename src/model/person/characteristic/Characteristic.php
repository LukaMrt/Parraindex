<?php

namespace App\model\person\characteristic;


class Characteristic {

	private int $id;
	private string $title;
	private CharacteristicType $type;
	private string $url;
	private string $image;
	private bool $visible;
	private string $value;

	public function __construct(CharacteristicBuilder $builder) {
		$this->id = $builder->getId();
		$this->title = $builder->getTitle();
		$this->type = $builder->getType();
		$this->url = $builder->getUrl();
		$this->image = $builder->getImage();
		$this->visible = $builder->isVisible();
		$this->value = $builder->getValue();
	}

}