<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

readonly class UserDTO
{
    /**
     * @param array<RoleDTO> $roles
     * @param array<string, mixed> $preferences
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $status,
        public array $roles = [],
        public array $preferences = [],
        public ?string $phone = null,
        public ?string $avatar = null,
        public ?string $language = null,
        public ?string $timezone = null,
        public bool $isEmailVerified = false,
        public bool $isTwoFactorEnabled = false,
        public ?\DateTimeImmutable $lastLoginAt = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            status: $data['status'],
            roles: isset($data['roles'])
                ? array_map(fn (array $role) => RoleDTO::fromArray($role), $data['roles'])
                : [],
            preferences: $data['preferences'] ?? [],
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            language: $data['language'] ?? null,
            timezone: $data['timezone'] ?? null,
            isEmailVerified: $data['is_email_verified'] ?? false,
            isTwoFactorEnabled: $data['is_two_factor_enabled'] ?? false,
            lastLoginAt: isset($data['last_login_at']) ? new \DateTimeImmutable($data['last_login_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'status' => $this->status,
            'roles' => array_map(fn (RoleDTO $role) => $role->toArray(), $this->roles),
            'preferences' => $this->preferences,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'is_email_verified' => $this->isEmailVerified,
            'is_two_factor_enabled' => $this->isTwoFactorEnabled,
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function hasRole(string $roleName): bool
    {
        return array_reduce(
            $this->roles,
            fn (bool $carry, RoleDTO $role) => $carry || $role->name === $roleName,
            false
        );
    }
}
