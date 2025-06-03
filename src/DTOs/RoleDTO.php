<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

class RoleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $display_name,
        public readonly string $description,
        public readonly array $permissions,
        public readonly bool $is_system,
        public readonly string $created_at,
        public readonly string $updated_at,
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
            name: (string) $data['name'],
            display_name: (string) $data['display_name'],
            description: (string) $data['description'],
            permissions: (array) $data['permissions'],
            is_system: (bool) $data['is_system'],
            created_at: (string) $data['created_at'],
            updated_at: (string) $data['updated_at'],
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
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'is_system' => $this->is_system,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
