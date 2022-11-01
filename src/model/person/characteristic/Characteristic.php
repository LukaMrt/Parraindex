<?php

namespace App\model\person\characteristic;

use App\model\utils\Id;
use App\model\utils\Image;
use App\model\utils\Url;

class Characteristic {

    private Id $id;
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