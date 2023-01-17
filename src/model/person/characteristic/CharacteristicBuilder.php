<?php

namespace App\model\person\characteristic;

/**
 * Builder instance for {@see Characteristic}.
 */
class CharacteristicBuilder
{
    /**
     * @var int Id of the characteristic
     */
    private int $id;
    /**
     * @var string Name of the characteristic
     */
    private string $title;
    /**
     * @var CharacteristicType Type of the characteristic
     */
    private CharacteristicType $type;
    /**
     * @var string Target url of the characteristic
     */
    private string $url;
    /**
     * @var string Image url of the characteristic
     */
    private string $image;
    /**
     * @var bool Is the characteristic visible
     */
    private bool $visible;
    /**
     * @var string Description of the characteristic
     */
    private string $value;


    /**
     * Sets the id of the characteristic
     * @param int $id Id of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withId(int $id): CharacteristicBuilder
    {
        $this->id = $id;
        return $this;
    }


    /**
     * Sets the name of the characteristic
     * @param string $title Name of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withTitle(string $title): CharacteristicBuilder
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Sets the type of the characteristic
     * @param string $typeName Type of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withType(string $typeName): CharacteristicBuilder
    {
        $this->type = CharacteristicType::fromName($typeName);
        return $this;
    }


    /**
     * Sets the target url of the characteristic
     * @param string $url Target url of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withUrl(string $url): CharacteristicBuilder
    {
        $this->url = $url;
        return $this;
    }


    /**
     * Sets the image url of the characteristic
     * @param string $image Image url of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withImage(string $image): CharacteristicBuilder
    {
        $this->image = $image;
        return $this;
    }


    /**
     * Sets the visibility of the characteristic
     * @param bool $visible Is the characteristic visible
     * @return CharacteristicBuilder This to chain calls
     */
    public function withVisibility(bool $visible): CharacteristicBuilder
    {
        $this->visible = $visible;
        return $this;
    }


    /**
     * Sets the description of the characteristic
     * @param string $value Description of the characteristic
     * @return CharacteristicBuilder This to chain calls
     */
    public function withValue(string $value): CharacteristicBuilder
    {
        $this->value = $value;
        return $this;
    }


    /**
     * @return int Id of the characteristic
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string Name of the characteristic
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * @return CharacteristicType Type of the characteristic
     */
    public function getType(): CharacteristicType
    {
        return $this->type;
    }


    /**
     * @return string Target url of the characteristic
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * @return string Image url of the characteristic
     */
    public function getImage(): string
    {
        return $this->image;
    }


    /**
     * @return bool Is the characteristic visible
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }


    /**
     * @return string Description of the characteristic
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * @return Characteristic Characteristic instance build from the builder
     */
    public function build(): Characteristic
    {
        return new Characteristic($this);
    }
}
