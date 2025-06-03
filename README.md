# Lumexa Auth SDK

Ce SDK permet d'interagir facilement avec le service d'authentification de Lumexa.

## Installation

```bash
composer require lumexa/auth-sdk
```

## Configuration

Pour utiliser le SDK, vous devez créer une instance du client avec votre token de store :

```php
use Lumexa\AuthSdk\AuthClient;

$client = new AuthClient(
    baseUrl: 'https://api.lumexa.com',
    storeToken: 'votre-store-token' // Token unique de votre store
);
```

## Authentification

### Inscription d'un utilisateur

```php
$result = $client->register([
    'email' => 'utilisateur@example.com',
    'password' => 'motdepasse123',
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'phone' => '+33123456789'
]);
```

### Connexion

```php
$token = $client->login('utilisateur@example.com', 'motdepasse123');
```

### Déconnexion

```php
$client->logout();
```

### Récupération de l'utilisateur courant

```php
$user = $client->getCurrentUser();
```

### Gestion du mot de passe

```php
// Changement de mot de passe
$client->changePassword('ancien_mot_de_passe', 'nouveau_mot_de_passe');

// Demande de réinitialisation
$client->requestPasswordReset('utilisateur@example.com');

// Réinitialisation avec token
$client->resetPassword('token_recu_par_email', 'nouveau_mot_de_passe');
```

### Vérification d'email

```php
// Vérifier l'email avec un token
$client->verifyEmail('token_de_verification');

// Renvoyer l'email de vérification
$client->resendVerification();
```

## Gestion des utilisateurs

### Liste des utilisateurs

```php
$users = $client->getAllUsers();
```

### Récupérer un utilisateur

```php
$user = $client->getUserById('user_id');
```

### Créer un utilisateur

```php
$user = $client->createUser([
    'email' => 'utilisateur@example.com',
    'password' => 'motdepasse123',
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'phone' => '+33123456789'
]);
```

## Gestion des rôles

### Liste des rôles

```php
// Tous les rôles
$roles = $client->getAllRoles();

// Avec pagination
$roles = $client->getRoles(page: 1, perPage: 20);
```

### Opérations sur les rôles

```php
// Créer un rôle
$role = $client->createRole([
    'name' => 'admin',
    'display_name' => 'Administrateur',
    'description' => 'Accès complet au système',
    'permissions' => ['users.manage', 'roles.manage']
]);

// Mettre à jour un rôle
$role = $client->updateRole($roleId, [
    'display_name' => 'Super Administrateur',
    'permissions' => ['users.manage', 'roles.manage', 'system.manage']
]);

// Supprimer un rôle
$client->deleteRole($roleId);
```

### Attribution des rôles

```php
// Assigner un rôle à un utilisateur
$client->assignRole($userId, $roleId);

// Retirer un rôle d'un utilisateur
$client->removeRole($userId, $roleId);

// Récupérer les rôles d'un utilisateur
$roles = $client->getUserRoles($userId);
```

## Gestion des permissions

```php
// Liste toutes les permissions disponibles
$permissions = $client->getPermissions();
```

## Gestion des erreurs

Le SDK utilise des exceptions typées pour la gestion des erreurs. Toutes les erreurs liées à l'authentification lèvent une `AuthException` :

```php
use Lumexa\AuthSdk\Exceptions\AuthException;

try {
    $user = $client->getCurrentUser();
} catch (AuthException $e) {
    // Gérer l'erreur
    echo $e->getMessage();
}
```

## DTOs disponibles

Le SDK utilise des DTOs (Data Transfer Objects) pour représenter les données :

### UserDTO

```php
$user->id;          // string
$user->first_name;  // string
$user->last_name;   // string
$user->email;       // string
$user->phone;       // string
$user->roles;       // array<RoleDTO>
```

### RoleDTO

```php
$role->id;           // string
$role->name;         // string
$role->display_name; // string
$role->description;  // string
$role->permissions;  // array<string>
$role->is_system;    // bool
$role->created_at;   // string
$role->updated_at;   // string

// Vérifier si un rôle a une permission spécifique
$role->hasPermission('users.manage'); // bool
```

## Support

Pour toute question ou problème, veuillez ouvrir une issue sur le dépôt GitHub du projet.
