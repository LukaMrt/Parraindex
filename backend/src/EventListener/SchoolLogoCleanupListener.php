<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Person\School;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsEntityListener(event: Events::preUpdate, entity: School::class)]
#[AsEntityListener(event: Events::postUpdate, entity: School::class)]
#[AsEntityListener(event: Events::postRemove, entity: School::class)]
final class SchoolLogoCleanupListener
{
    private ?string $oldLogo = null;

    public function preUpdate(School $school, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('logo')) {
            $oldValue      = $args->getOldValue('logo');
            $this->oldLogo = is_string($oldValue) ? $oldValue : null;
        }
    }

    public function postUpdate(): void
    {
        $this->deleteFile($this->oldLogo);
        $this->oldLogo = null;
    }

    public function postRemove(School $school): void
    {
        $this->deleteFile($school->getLogo());
    }

    private function deleteFile(?string $filename): void
    {
        if ($filename === null) {
            return;
        }

        $path = __DIR__ . '/../../public/uploads/schools/' . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}
