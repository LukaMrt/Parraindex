<?php

namespace App\model\user\characteristic;

use App\model\utils\Id;
use App\model\utils\Image;
use App\model\utils\Url;

class Characteristic {

    private Id $id;
    private Title $title;
    private CharacteristicType $type;
    private Url $url;
    private Image $image;
    private bool $visible;
    private Value $value;

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