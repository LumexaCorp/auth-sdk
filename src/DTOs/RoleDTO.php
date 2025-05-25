<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk\DTOs;

readonly class RoleDTO
{
    /**
     * @param array<string> $permissions
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $displayName,
        public string $description,
        public array $permissions = [],
        public bool $isSystem = false,
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
            name: $data['name'],
            displayName: $data['display_name'],
            description: $data['description'],
            permissions: $data['permissions'] ?? [],
            isSystem: $data['is_system'] ?? false,
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
            'name' => $this->name,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'is_system' => $this->isSystem,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
