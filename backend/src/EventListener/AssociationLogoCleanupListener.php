<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Person\Association;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsEntityListener(event: Events::preUpdate, entity: Association::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Association::class)]
#[AsEntityListener(event: Events::postRemove, entity: Association::class)]
final class AssociationLogoCleanupListener
{
    private ?string $oldLogo = null;

    public function preUpdate(Association $association, PreUpdateEventArgs $args): void
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

    public function postRemove(Association $association): void
    {
        $this->deleteFile($association->getLogo());
    }

    private function deleteFile(?string $filename): void
    {
        if ($filename === null) {
            return;
        }

        $path = __DIR__ . '/../../public/uploads/associations/' . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}
