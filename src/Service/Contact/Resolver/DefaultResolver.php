<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class DefaultResolver implements ContactResolverInterface
{
    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::BUG
            || $contact->getType() === Type::OTHER;
    }

    public function resolve(Contact $contact): ?Response
    {
        return null;
    }
}
