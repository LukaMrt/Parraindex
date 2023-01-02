<?php

namespace App\model\person\characteristic;

class Characteristic
{
    private int $id;
    private string $title;
    private CharacteristicType $type;
    private string $url;
    private string $image;
    private bool $visible;
    private ?string $value;

    public function __construct(CharacteristicBuilder $builder)
    {
        $this->id = $builder->getId();
        $this->title = $builder->getTitle();
        $this->type = $builder->getType();
        $this->url = $builder->getUrl();
        $this->image = $builder->getImage();
        $this->visible = $builder->isVisible();
        $this->value = $builder->getValue();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Set the new value of the characteristic
     *
     * @param string $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Set the new visibility of the characteristic
     *
     * @param bool $visible
     * @return void
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getPrefix(): string
    {
        return $this->type->getPrefix();
    }
}
