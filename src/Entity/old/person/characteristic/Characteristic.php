<?php

declare(strict_types=1);

namespace App\Entity\old\person\characteristic;

use JsonSerializable;

/**
 * Person characteristic (networks, personal information...)
 */
class Characteristic implements JsonSerializable
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
    private CharacteristicType $characteristicType;

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
     * @var string|null Description of the characteristic
     */
    private ?string $value;


    /**
     * @param CharacteristicBuilder $characteristicBuilder Builder of the characteristic
     */
    public function __construct(CharacteristicBuilder $characteristicBuilder)
    {
        $this->id      = $characteristicBuilder->getId();
        $this->title   = $characteristicBuilder->getTitle();
        $this->characteristicType    = $characteristicBuilder->getType();
        $this->url     = $characteristicBuilder->getUrl();
        $this->image   = $characteristicBuilder->getImage();
        $this->visible = $characteristicBuilder->isVisible();
        $this->value   = $characteristicBuilder->getValue();
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
     * @return string Type of the characteristic
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
     * @return string|null Description of the characteristic
     */
    public function getValue(): ?string
    {
        return $this->value;
    }


    /**
     * Set the new value of the characteristic
     *
     * @param ?string $value New value of the characteristic
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }


    /**
     * @return bool True if the characteristic is visible, false otherwise
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }


    /**
     * Set the new visibility of the characteristic
     *
     * @param bool $visible New visibility of the characteristic
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }


    /**
     * @return string Prefix of the characteristic
     */
    public function getPrefix(): string
    {
        return $this->characteristicType->getPrefix();
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
