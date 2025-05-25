# Auth SDK Lumexa

Ce SDK fournit une interface pour interagir avec le service d'authentification de Lumexa.

## Installation

```bash
composer require lumexa/auth-sdk
```

## Configuration

Pour utiliser le SDK, vous devez d'abord initialiser le client avec vos informations d'authentification :

```php
use Lumexa\AuthSdk\AuthClient;

$authClient = new AuthClient(
    baseUrl: 'https://api.lumexa.com',
    apiKey: 'votre-api-key',
    storeToken: 'votre-store-token'
);
```

## Fonctionnalités disponibles

### Authentification

#### Login
```php
$tokenDTO = $authClient->login('email@example.com', 'mot-de-passe');
// Retourne un TokenDTO contenant access_token et refresh_token
```

#### Inscription
```php
$userData = [
    'email' => 'email@example.com',
    'password' => 'mot-de-passe',
    'name' => 'John Doe'
];
$userDTO = $authClient->register($userData);
```

#### Rafraîchissement du token
```php
$newTokenDTO = $authClient->refreshToken('votre-refresh-token');
```

#### Déconnexion
```php
$authClient->logout();
```

### Gestion du profil utilisateur

#### Obtenir l'utilisateur courant
```php
$userDTO = $authClient->getCurrentUser();
```

#### Mettre à jour le profil
```php
$data = [
    'name' => 'Nouveau Nom',
    'phone' => '0123456789'
];
$updatedUser = $authClient->updateProfile($data);
```

### Gestion du mot de passe

#### Changer le mot de passe
```php
$authClient->changePassword('ancien-mot-de-passe', 'nouveau-mot-de-passe');
```

#### Demander une réinitialisation de mot de passe
```php
$authClient->requestPasswordReset('email@example.com');
```

#### Réinitialiser le mot de passe
```php
$authClient->resetPassword('token-de-reset', 'nouveau-mot-de-passe');
```

### Gestion des rôles

#### Obtenir tous les rôles
```php
$roles = $authClient->getRoles(page: 1, perPage: 20);
```

#### Obtenir un rôle spécifique
```php
$role = $authClient->getRole(roleId: 1);
```

#### Créer un nouveau rôle
```php
$roleData = [
    'name' => 'admin',
    'description' => 'Administrateur'
];
$newRole = $authClient->createRole($roleData);
```

#### Mettre à jour un rôle
```php
$roleData = [
    'description' => 'Nouvelle description'
];
$updatedRole = $authClient->updateRole(roleId: 1, $roleData);
```

#### Supprimer un rôle
```php
$authClient->deleteRole(roleId: 1);
```

### Gestion des rôles utilisateur

#### Assigner un rôle à un utilisateur
```php
$authClient->assignRole(userId: 1, roleId: 2);
```

#### Retirer un rôle d'un utilisateur
```php
$authClient->removeRole(userId: 1, roleId: 2);
```

#### Obtenir les rôles d'un utilisateur
```php
$userRoles = $authClient->getUserRoles(userId: 1);
```

### Vérification d'email

#### Vérifier l'email
```php
$authClient->verifyEmail('token-de-verification');
```

#### Renvoyer l'email de vérification
```php
$authClient->resendVerification();
```

## Gestion des erreurs

Toutes les méthodes peuvent lever une `AuthException` en cas d'erreur. Il est recommandé de gérer ces exceptions dans votre code :

```php
use Lumexa\AuthSdk\Exceptions\AuthException;

try {
    $user = $authClient->getCurrentUser();
} catch (AuthException $e) {
    // Gérer l'erreur
    echo $e->getMessage();
}
```
