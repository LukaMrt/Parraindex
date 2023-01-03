<?php

namespace App\model\person\characteristic;

use LogicException;

/**
 * Builder instance for {@see Characteristic}.
 */
class CharacteristicBuilder
{

    /** @var int $id */
    private int $id;

    /** @var string $title */
    private string $title;

    /** @var CharacteristicType $type */
    private CharacteristicType $type;

    /** @var string $url */
    private string $url;

    /** @var string $image */
    private string $image;

    /** @var bool $visible */
    private bool $visible;

    /** @var string $value */
    private string $value;


    /**
     * @param int $id Set id property.
     * @return $this Builder instance.
     */
    public function withId(int $id): CharacteristicBuilder
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @param string $title Set title property.
     * @return $this Builder instance.
     */
    public function withTitle(string $title): CharacteristicBuilder
    {
        $this->title = $title;
        return $this;
    }


    /**
     * @param CharacteristicType $type Set type property.
     * @return $this Builder instance.
     */
    public function withType(string $typeName): CharacteristicBuilder
    {
        $this->type = CharacteristicType::fromName($typeName);
        return $this;
    }


    /**
     * @param string $url Set url property.
     * @return $this Builder instance.
     */
    public function withUrl(string $url): CharacteristicBuilder
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @param string $image Set image property.
     * @return $this Builder instance.
     */
    public function withImage(string $image): CharacteristicBuilder
    {
        $this->image = $image;
        return $this;
    }


    /**
     * @param bool $visible Set visible property.
     * @return $this Builder instance.
     */
    public function withVisibility(bool $visible): CharacteristicBuilder
    {
        $this->visible = $visible;
        return $this;
    }


    /**
     * @param string $value Set value property.
     * @return $this Builder instance.
     */
    public function withValue(string $value): CharacteristicBuilder
    {
        $this->value = $value;
        return $this;
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * @return CharacteristicType
     */
    public function getType(): CharacteristicType
    {
        return $this->type;
    }


    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }


    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }


    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * @return Characteristic New instance from Builder.
     * @throws LogicException if Builder does not validate.
     */
    public function build(): Characteristic
    {
        return new Characteristic($this);
    }

}
