<?php

namespace App\model\user\characteristic;

use App\model\utils\Id;
use App\model\utils\Image;
use App\model\utils\Url;

/**
 * Builder instance for {@see Characteristic}.
 */
class CharacteristicBuilder {

    /** @var Id $id */
    private Id $id;

    /** @var Title $title */
    private Title $title;

    /** @var CharacteristicType $type */
    private CharacteristicType $type;

    /** @var Url $url */
    private Url $url;

    /** @var Image $image */
    private Image $image;

    /** @var bool $visible */
    private bool $visible;

    /** @var Value $value */
    private Value $value;

    /**
     * @param Id $id Set id property.
     * @return $this Builder instance.
     */
    public function withId(Id $id): CharacteristicBuilder {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Title $title Set title property.
     * @return $this Builder instance.
     */
    public function withTitle(Title $title): CharacteristicBuilder {
        $this->title = $title;
        return $this;
    }

    /**
     * @param CharacteristicType $type Set type property.
     * @return $this Builder instance.
     */
    public function withType(CharacteristicType $type): CharacteristicBuilder {
        $this->type = $type;
        return $this;
    }

    /**
     * @param Url $url Set url property.
     * @return $this Builder instance.
     */
    public function withUrl(Url $url): CharacteristicBuilder {
        $this->url = $url;
        return $this;
    }

    /**
     * @param Image $image Set image property.
     * @return $this Builder instance.
     */
    public function withImage(Image $image): CharacteristicBuilder {
        $this->image = $image;
        return $this;
    }

    /**
     * @param bool $visible Set visible property.
     * @return $this Builder instance.
     */
    public function withVisible(bool $visible): CharacteristicBuilder {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @param Value $value Set value property.
     * @return $this Builder instance.
     */
    public function withValue(Value $value): CharacteristicBuilder {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Id
     */
    public function getId(): Id {
        return $this->id;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title {
        return $this->title;
    }

    /**
     * @return CharacteristicType
     */
    public function getType(): CharacteristicType {
        return $this->type;
    }

    /**
     * @return Url
     */
    public function getUrl(): Url {
        return $this->url;
    }

    /**
     * @return Image
     */
    public function getImage(): Image {
        return $this->image;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool {
        return $this->visible;
    }

    /**
     * @return Value
     */
    public function getValue(): Value {
        return $this->value;
    }

    /**
     * @return Characteristic New instance from Builder.
     * @throws \LogicException if Builder does not validate.
     */
    public function build(): Characteristic {

        return new Characteristic($this);
    }

}
