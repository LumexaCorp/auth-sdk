<?php

declare(strict_types=1);

namespace Lumexa\AuthSdk;

use GuzzleHttp\Client;
use Lumexa\AuthSdk\DTOs\RoleDTO;
use Lumexa\AuthSdk\DTOs\UserDTO;
use Lumexa\AuthSdk\DTOs\TokenDTO;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use Lumexa\AuthSdk\Exceptions\AuthException;
use Lumexa\AuthSdk\Exceptions\ValidationException;

class AuthClient
{
    private Client $httpClient;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $storeToken,
        ?Client $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'X-Store-Token' => $this->storeToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Handle API errors and transform them into appropriate exceptions
     *
     * @throws ValidationException|AuthException
     */
    private function handleApiError(\Throwable $e): never
    {
        if ($e instanceof ClientException) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            if ($statusCode === 422 && isset($body['errors'])) {
                throw new ValidationException(
                    $body['message'] ?? 'Validation failed',
                    $body['errors'],
                    $statusCode,
                    $e
                );
            }

            if (isset($body['message'])) {
                throw new AuthException($body['message'], $statusCode, $e);
            }
        }

        throw new AuthException($e->getMessage(), (int) $e->getCode(), $e);
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
            return TokenDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            $data = json_decode((string) $response->getBody(), true);
            return $data['data'];
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return TokenDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
        }
    }

    /**
     * Get current user information
     *
     * @throws AuthException
     */
    public function getCurrentUser(string $token): UserDTO
    {
        try {
            $response = $this->httpClient->get('/api/auth/me', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return UserDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return RoleDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return RoleDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return RoleDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return $data['data']['permissions'];
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return array_map(fn (array $item) => RoleDTO::fromArray($item), $data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return UserDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            $response = $this->httpClient->post('/api/auth/users', [
                'json' => $data,
            ]);
            $data = json_decode((string) $response->getBody(), true);
            return UserDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return UserDTO::fromArray($data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
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
            return array_map(fn (array $role) => RoleDTO::fromArray($role), $data['data']);
        } catch (\Throwable $e) {
            $this->handleApiError($e);
        }
    }

    public function getUserByEmail(string $email): ?UserDTO
    {
        try {
            $response = $this->httpClient->get("/api/auth/users/email/{$email}");
            $data = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() === 404) {
                return null;
            }

            return isset($data['data']) ? UserDTO::fromArray($data['data']) : null;
        } catch (\Throwable $e) {
            $this->handleApiError($e);
        }
    }
}
