<?php

/**
 * Note: Although Laravel's __() helper is typed as array|string|null,
 * this field always receives a string translation in practice.
 * This is a known false positive for static analysis tools.
 */

namespace LiviuVoica\LbContact\DTO;

final class NotificationServiceDTO
{
    public function __construct(
        public string $application_name,
        public string $subject,
        public string $from,
        public string $to,
        public string $full_name,
        public string $contact_subject,
        public ?string $message = null,
        public ?string $response = null,
        public ?string $cookie_visitor_uuid = null,
    ) {}

    /**
     * Create DTO from array payload.
     *
     * @param array{
     *     application_name: string,
     *     subject: string,
     *     from: string,
     *     to: string,
     *     full_name: string,
     *     contact_subject: string,
     *     message?: string|null,
     *     response?: string|null,
     *     cookie_visitor_uuid?: string|null
     * } $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            application_name: $payload['application_name'],
            subject: $payload['subject'],
            from: $payload['from'],
            to: $payload['to'],
            full_name: $payload['full_name'],
            contact_subject: $payload['contact_subject'],
            message: $payload['message'] ?? null,
            response: $payload['response'] ?? null,
            cookie_visitor_uuid: $payload['cookie_visitor_uuid'] ?? null,
        );
    }
}
