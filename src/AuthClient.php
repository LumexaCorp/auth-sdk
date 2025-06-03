<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk;

use GuzzleHttp\Client;
use Lumexa\AuthSdk\Exceptions\AuthException;
use Lumexa\AuthSdk\DTOs\UserDTO;
use Lumexa\AuthSdk\DTOs\TokenDTO;
use Lumexa\AuthSdk\DTOs\RoleDTO;

class AuthClient
{
    private Client $httpClient;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $storeToken,
        ?Client $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'X-Store-Domain' => $this->storeToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Login with email and password
     *
     * @throws AuthException
     */
    public function login(string $email, string $password): TokenDTO
    {
        try {
            $response = $this->httpClient->post('/api/auth/login', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return TokenDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to login: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Register a new user
     *
     * @param array{
     *     email: string,
     *     password: string,
     *     first_name: string,
     *     last_name: string,
     *     phone: string
     * } $data
     * @throws AuthException
     */
    public function register(array $data): array
    {
        try {
            $response = $this->httpClient->post('/api/auth/register', [
                'json' => $data,
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            throw new AuthException("Failed to register user: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Refresh access token
     *
     * @throws AuthException
     */
    public function refreshToken(string $refreshToken): TokenDTO
    {
        try {
            $response = $this->httpClient->post('/api/auth/refresh', [
                'json' => [
                    'refresh_token' => $refreshToken,
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return TokenDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to refresh token: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get current user information
     *
     * @throws AuthException
     */
    public function getCurrentUser(): UserDTO
    {
        try {
            $response = $this->httpClient->get('/api/auth/me');
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get current user: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Update user profile
     *
     * @param array<string, mixed> $data
     * @throws AuthException
     */
    public function updateProfile(array $data): UserDTO
    {
        try {
            $response = $this->httpClient->patch('/api/auth/profile', [
                'json' => $data,
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to update profile: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Change password
     *
     * @throws AuthException
     */
    public function changePassword(string $currentPassword, string $newPassword): void
    {
        try {
            $this->httpClient->post('/api/auth/password', [
                'json' => [
                    'current_password' => $currentPassword,
                    'new_password' => $newPassword,
                ],
            ]);
        } catch (\Exception $e) {
            throw new AuthException("Failed to change password: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Request password reset
     *
     * @throws AuthException
     */
    public function requestPasswordReset(string $email): void
    {
        try {
            $this->httpClient->post('/api/auth/password/reset', [
                'json' => ['email' => $email],
            ]);
        } catch (\Exception $e) {
            throw new AuthException("Failed to request password reset: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Reset password with token
     *
     * @throws AuthException
     */
    public function resetPassword(string $token, string $newPassword): void
    {
        try {
            $this->httpClient->post('/api/auth/password/reset/confirm', [
                'json' => [
                    'token' => $token,
                    'password' => $newPassword,
                ],
            ]);
        } catch (\Exception $e) {
            throw new AuthException("Failed to reset password: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get all roles with pagination
     *
     * @return array<RoleDTO>
     * @throws AuthException
     */
    public function getRoles(int $page = 1, int $perPage = 20): array
    {
        try {
            $response = $this->httpClient->get('/api/auth/roles', [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return array_map(fn (array $item) => RoleDTO::fromArray($item), $data['data']);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get roles: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get a role by ID
     *
     * @throws AuthException
     */
    public function getRole(int $roleId): RoleDTO
    {
        try {
            $response = $this->httpClient->get("/api/auth/roles/{$roleId}");
            $data = json_decode((string) $response->getBody(), true);
            return RoleDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Create a new role
     *
     * @param array{
     *     name: string,
     *     display_name: string,
     *     description: string,
     *     permissions: array<string>
     * } $data
     * @throws AuthException
     */
    public function createRole(array $data): RoleDTO
    {
        try {
            $response = $this->httpClient->post('/api/auth/roles', [
                'json' => $data,
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return RoleDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to create role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Update a role
     *
     * @param array{
     *     name?: string,
     *     display_name?: string,
     *     description?: string,
     *     permissions?: array<string>
     * } $data
     * @throws AuthException
     */
    public function updateRole(int $roleId, array $data): RoleDTO
    {
        try {
            $response = $this->httpClient->patch("/api/auth/roles/{$roleId}", [
                'json' => $data,
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return RoleDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to update role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Delete a role
     *
     * @throws AuthException
     */
    public function deleteRole(int $roleId): void
    {
        try {
            $this->httpClient->delete("/api/auth/roles/{$roleId}");
        } catch (\Exception $e) {
            throw new AuthException("Failed to delete role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Assign role to user
     *
     * @throws AuthException
     */
    public function assignRole(int $userId, int $roleId): void
    {
        try {
            $this->httpClient->post("/api/auth/users/{$userId}/roles", [
                'json' => ['role_id' => $roleId],
            ]);
        } catch (\Exception $e) {
            throw new AuthException("Failed to assign role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Remove role from user
     *
     * @throws AuthException
     */
    public function removeRole(int $userId, int $roleId): void
    {
        try {
            $this->httpClient->delete("/api/auth/users/{$userId}/roles/{$roleId}");
        } catch (\Exception $e) {
            throw new AuthException("Failed to remove role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get all available permissions
     *
     * @return array<string>
     * @throws AuthException
     */
    public function getPermissions(): array
    {
        try {
            $response = $this->httpClient->get('/api/auth/permissions');
            $data = json_decode((string) $response->getBody(), true);
            return $data['permissions'];
        } catch (\Exception $e) {
            throw new AuthException("Failed to get permissions: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get user roles
     *
     * @return array<RoleDTO>
     * @throws AuthException
     */
    public function getUserRoles(int $userId): array
    {
        try {
            $response = $this->httpClient->get("/api/auth/users/{$userId}/roles");
            $data = json_decode((string) $response->getBody(), true);
            return array_map(fn (array $item) => RoleDTO::fromArray($item), $data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get user roles: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Verify email address
     *
     * @throws AuthException
     */
    public function verifyEmail(string $token): void
    {
        try {
            $this->httpClient->post('/api/auth/email/verify', [
                'json' => ['token' => $token],
            ]);
        } catch (\Exception $e) {
            throw new AuthException("Failed to verify email: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Resend email verification
     *
     * @throws AuthException
     */
    public function resendVerification(): void
    {
        try {
            $this->httpClient->post('/api/auth/email/verification');
        } catch (\Exception $e) {
            throw new AuthException("Failed to resend verification email: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Logout user (invalidate token)
     *
     * @throws AuthException
     */
    public function logout(): void
    {
        try {
            $this->httpClient->post('/api/auth/logout');
        } catch (\Exception $e) {
            throw new AuthException("Failed to logout: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get all users
     *
     * @return array<UserDTO>
     * @throws AuthException
     */
    public function getAllUsers(): array
    {
        try {
            $response = $this->httpClient->get('/api/users');
            $data = json_decode((string) $response->getBody(), true);
            return array_map(fn (array $user) => UserDTO::fromArray($user), $data['data']);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get users: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get a user by ID
     *
     * @throws AuthException
     */
    public function getUserById(string $id): UserDTO
    {
        try {
            $response = $this->httpClient->get("/api/users/{$id}");
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get user: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Create a new user
     *
     * @param array{
     *     email: string,
     *     password: string,
     *     first_name: string,
     *     last_name: string,
     *     phone: string
     * } $data
     * @throws AuthException
     */
    public function createUser(array $data): UserDTO
    {
        try {
            $response = $this->httpClient->post('/api/users', [
                'json' => $data,
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to create user: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get all roles
     *
     * @return array<RoleDTO>
     * @throws AuthException
     */
    public function getAllRoles(): array
    {
        try {
            $response = $this->httpClient->get('/api/roles');
            $data = json_decode((string) $response->getBody(), true);
            return array_map(fn (array $role) => RoleDTO::fromArray($role), $data['data']);
        } catch (\Exception $e) {
            throw new AuthException("Failed to get roles: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Assign a role to a user
     *
     * @throws AuthException
     */
    public function assignRole(string $userId, string $roleId): UserDTO
    {
        try {
            $response = $this->httpClient->post("/api/users/{$userId}/roles", [
                'json' => ['role_id' => $roleId],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to assign role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Remove a role from a user
     *
     * @throws AuthException
     */
    public function removeRole(string $userId, string $roleId): array
    {
        try {
            $response = $this->httpClient->delete("/api/users/{$userId}/roles/{$roleId}");
            $data = json_decode((string) $response->getBody(), true);
            return array_map(fn (array $role) => RoleDTO::fromArray($role), $data);
        } catch (\Exception $e) {
            throw new AuthException("Failed to remove role: {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}
