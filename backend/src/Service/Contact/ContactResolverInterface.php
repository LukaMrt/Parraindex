<?php

declare(strict_types=1);

namespace App\Service\Contact;

use App\Entity\Contact\Contact;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for contact resolution strategies.
 */
interface ContactResolverInterface
{
    /**
     * Check if this resolver supports the given contact type.
     */
    public function supports(Contact $contact): bool;

    /**
     * Resolve the contact.
     *
     * @return Response|null Return a Response to redirect, or null to continue with default success message
     */
    public function resolve(Contact $contact): ?Response;
}
