# User Data Scoping Implementation

## Overview

This document explains the implementation of user-specific data scoping in the application. The system ensures that:

- **Superadmin users** can see ALL data from all users
- **Regular users** can only see THEIR OWN data

## What Was Implemented

### 1. Database Changes (Migration: `2026_02_07_000001_add_user_id_to_main_tables.php`)

Added `user_id` foreign key column to the following tables:
- `bon_commande_clients`
- `bon_commande_fournisseurs`
- `bon_livraison_clients`
- `bon_achat_fournisseur`
- `reglements_clients`
- `reglements_fournisseurs`
- `clients`
- `fournisseurs`

All existing records were automatically assigned to the superadmin user to maintain data integrity.

### 2. BelongsToUser Trait (`app/Traits/BelongsToUser.php`)

Created a reusable trait that provides:

#### Automatic User ID Assignment
When a new record is created, the `user_id` is automatically set to the authenticated user:

```php
// No need to manually set user_id
$client = Client::create([
    'code_client' => 'CL001',
    'raison_sociale' => 'Test Client',
    // user_id is automatically set!
]);
```

#### Automatic Data Filtering
All queries are automatically scoped to the current user's data:

```php
// Regular user will only see their own clients
$clients = Client::all();

// Superadmin will see ALL clients
$clients = Client::all(); // Returns all clients from all users
```

#### Additional Scopes
The trait provides helpful scopes for advanced queries:

```php
// Bypass user scope (useful for reports)
$allClients = Client::withAllUsers()->get();

// Get data for a specific user
$userClients = Client::forUser($userId)->get();
```

### 3. Updated Models

The following models now use the `BelongsToUser` trait:
- `BonCommandeClient`
- `BonCommandeFournisseur`
- `BonLivraisonClient`
- `BonAchatFournisseur`
- `Client`
- `Fournisseur`
- `ReglementClient`
- `ReglementFournisseur`

## How It Works

### For Superadmin Users

When a user has the `superadmin` role:
- They see **ALL records** from **ALL users**
- No filtering is applied
- They can manage data for any user

### For Regular Users

When a user has any other role (commercial, assistant, etc.):
- They see **ONLY their own records**
- Automatic filtering applies to all queries
- They cannot see or modify other users' data

## Testing

### Test as Superadmin
1. Log in as a superadmin user
2. Navigate to any section (Clients, Bon de Commande, etc.)
3. You should see all records from all users

### Test as Regular User
1. Create a new user with "commercial" or "assistant" role
2. Log in as that user
3. Create some records (clients, bon de commande, etc.)
4. Log out and log in as another regular user
5. The second user should NOT see the first user's data
6. Each user should only see their own data

### Test Data Creation
1. Log in as a regular user
2. Create a new client or bon de commande
3. Log in as superadmin
4. Check the record in the database - `user_id` should be set to the user who created it

## Code Examples

### Creating Records
```php
// In any controller, just create records normally
// user_id is automatically set
$bonCommande = BonCommandeClient::create([
    'numero_bon' => 'BC-2026/0001',
    'date' => now(),
    'client_id' => $clientId,
    // ... other fields
]);
```

### Querying Records
```php
// Regular queries automatically filter by user
$bonCommandes = BonCommandeClient::where('statut', 'Validé')->get();
// Regular user sees only their validated records
// Superadmin sees ALL validated records

// To explicitly bypass the scope (e.g., for admin reports)
$allBonCommandes = BonCommandeClient::withAllUsers()
    ->where('statut', 'Validé')
    ->get();
```

### Relationships
```php
// Relationships work normally
$client = Client::with('bonCommandes')->find($id);
// Regular user can only access their own clients
// and will only see bon_commandes that belong to them

// Superadmin sees everything
```

## Important Notes

### 1. API Controllers
All API controllers automatically benefit from this scoping. No changes needed in controller code.

### 2. Dashboard Statistics
If you need to show statistics for all users to superadmin, use `withAllUsers()` scope:

```php
// In DashboardController
$totalClients = auth()->user()->hasRole('superadmin')
    ? Client::withAllUsers()->count()
    : Client::count();
```

### 3. Reports and Exports
For reports that should show all users' data to superadmin:

```php
if (auth()->user()->hasRole('superadmin')) {
    $data = Model::withAllUsers()->get();
} else {
    $data = Model::all(); // Automatically scoped to current user
}
```

### 4. Seeding and Testing
When seeding data or running tests, make sure to set the `user_id` manually:

```php
Client::factory()->create([
    'user_id' => $userId,
    // ... other fields
]);
```

## Rollback

If you need to rollback this feature:

```bash
php artisan migrate:rollback
```

This will:
- Remove all `user_id` columns
- Remove the foreign key constraints
- Restore the database to its previous state

## Troubleshooting

### Users can't see their own data
1. Check that the user is authenticated
2. Verify `user_id` is set in the database for those records
3. Clear the cache: `php artisan cache:clear`

### Superadmin sees no data
1. Verify the user has the `superadmin` role assigned
2. Clear permission cache: `php artisan permission:cache-reset`
3. Check that the role name is exactly `superadmin` (case-sensitive)

### New records don't have user_id
1. Ensure the user is authenticated when creating records
2. Check that the model uses the `BelongsToUser` trait
3. Verify `user_id` is in the model's `$fillable` array

## Future Enhancements

Potential improvements:
1. Add user filter dropdown for superadmin in UI
2. Add audit log to track which user created/modified records
3. Add team/organization level scoping for larger deployments
4. Add user switching feature for superadmin to "view as" another user
