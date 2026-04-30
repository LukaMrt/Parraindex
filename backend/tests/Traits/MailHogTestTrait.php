<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @phpstan-ignore trait.unused
 */
trait MailHogTestTrait
{
    private function getLastEmailFromMailHog(): ?array
    {
        $client = HttpClient::create();

        try {
            $response = $client->request(Request::METHOD_GET, 'http://localhost:8025/api/v2/messages?limit=1');
            $data     = $response->toArray();
            return $data['items'][0] ?? null;
        } catch (TransportExceptionInterface) {
            return null;
        }
    }

    private function clearMailHog(): void
    {
        $client = HttpClient::create();

        try {
            $client->request(Request::METHOD_DELETE, 'http://localhost:8025/api/v1/messages');
            // phpcs:ignore
        } catch (TransportExceptionInterface) {
        }
    }

    private function assertEmailWasSent(string $to, string $subject): void
    {
        $email = $this->getLastEmailFromMailHog();

        self::assertNotNull($email, 'No email found in MailHog');
        self::assertStringContainsString($to, $email['To'][0]['Mailbox']);
        self::assertSame($subject, $email['Content']['Headers']['Subject'][0]);
    }
}
