<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

readonly class TokenDTO
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType,
        public int $expiresIn,
        public ?\DateTimeImmutable $createdAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'],
            tokenType: $data['token_type'],
            expiresIn: $data['expires_in'],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function getAuthorizationHeader(): string
    {
        return "{$this->tokenType} {$this->accessToken}";
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
