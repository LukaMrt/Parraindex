<?php

declare(strict_types=1);

namespace App\Service\Contact;

use App\Entity\Contact\Contact;
use Symfony\Component\HttpFoundation\Response;

final class ContactResolverManager
{
    /**
     * @param iterable<ContactResolverInterface> $resolvers
     */
    public function __construct(
        private iterable $resolvers,
    ) {
    }

    public function resolve(Contact $contact): ?Response
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($contact)) {
                return $resolver->resolve($contact);
            }
        }

        $type = $contact->getType();
        throw new \RuntimeException(sprintf(
            'No resolver found for contact type "%s"',
            $type !== null ? $type->value : 'null'
        ));
    }
}
