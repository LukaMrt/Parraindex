<?php

namespace App\application\contact\executor;

class ContactExecutors
{

    private array $executors;


    public function __construct(array $executors)
    {
        $this->executors = $executors;
    }


    public function getExecutorsById(int $id): array
    {
        return array_filter($this->executors, fn($executor) => $executor->getId() === $id);
    }

}
