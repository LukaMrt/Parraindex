<?php

namespace App\application\contact\executor;

/**
 * Wrapper to list all the contact executors
 */
class ContactExecutors
{
    /**
     * @var ContactExecutor[] $executors
     */
    private array $executors;


    /**
     * @param ContactExecutor[] $executors the list of contact
     */
    public function __construct(array $executors)
    {
        $this->executors = $executors;
    }


    /**
     * Filter the list of executors by the given type id
     * @param int $type the type id of contact used to filter the list
     * @return ContactExecutor[] the list of executors filtered by the given type
     */
    public function getExecutorsById(int $type): array
    {
        return array_filter($this->executors, fn($executor) => $executor->getId() === $type);
    }
}
