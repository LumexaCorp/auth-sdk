<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

class UserDTO
{
    /**
     * @param array<RoleDTO> $roles
     */
    public function __construct(
        public readonly string $id,
        public readonly string $last_name,
        public readonly string $first_name,
        public readonly string $email,
        public readonly string $phone,
        public readonly array $roles,
    ) {
    }

    /**
     * Create a DTO from an array
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            last_name: (string) $data['last_name'],
            first_name: (string) $data['first_name'],
            email: (string) $data['email'],
            phone: (string) $data['phone'],
            roles: array_map(fn (array $role) => RoleDTO::fromArray($role), $data['roles'] ?? []),
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
            'id' => $this->id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'roles' => array_map(fn (RoleDTO $role) => $role->toArray(), $this->roles),
        ];
    }
}
