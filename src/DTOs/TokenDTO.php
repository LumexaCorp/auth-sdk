<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

class TokenDTO
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $token_type,
        public readonly ?string $refresh_token,
        public readonly ?int $expires_in,
        public readonly UserDTO $user,
    ) {
    }

    /**
     * Create a DTO from an array
     *
     * @param array{
     *     access_token: string,
     *     token_type: string,
     *     refresh_token?: string|null,
     *     expires_in?: int|null,
     *     user: array<string, mixed>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            access_token: $data['access_token'],
            token_type: $data['token_type'],
            refresh_token: $data['refresh_token'] ?? null,
            expires_in: $data['expires_in'] ?? null,
            user: UserDTO::fromArray($data['user']),
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
            'refresh_token' => $this->refresh_token,
            'expires_in' => $this->expires_in,
            'user' => $this->user->toArray(),
        ];
    }

    /**
     * Get the full authorization header value
     */
    public function getAuthorizationHeader(): string
    {
        return "{$this->token_type} {$this->access_token}";
    }

    public function isExpired(): bool
    {
        if (!$this->createdAt) {
            return true;
        }

        $expirationDate = $this->createdAt->modify("+{$this->expiresIn} seconds");
        return $expirationDate <= new \DateTimeImmutable();
    }
}
