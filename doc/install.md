# Installing Spipu Configuration Bundle

[back](./README.md)

## Requirements

- PHP 8.1+
- Symfony 6.4+
- `spipu/core-bundle` (already installed)
- `spipu/ui-bundle` (already installed)

## Installation

```bash
composer require spipu/configuration-bundle
```

## Configuration

### 1. Register the bundle

In `config/bundles.php`:

```php
return [
    // ...
    Spipu\CoreBundle\SpipuCoreBundle::class => ['all' => true],
    Spipu\UiBundle\SpipuUiBundle::class => ['all' => true],
    Spipu\ConfigurationBundle\SpipuConfigurationBundle::class => ['all' => true],
];
```

### 2. Import the routes

In `config/routes.yaml`:

```yaml
spipu_configuration:
    resource: '@SpipuConfigurationBundle/config/routes.yaml'
```

This registers:
- `GET  /configuration/list/{scopeCode}` — admin list (name: `spipu_configuration_admin_list`)
- `GET|POST /configuration/show/{code}/{scopeCode}` — admin edit (name: `spipu_configuration_admin_edit`)

### 3. Run database migrations

The bundle provides a `spipu_configuration` table. Generate and run the migration:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 4. Define your configuration keys

Add a `spipu_configuration:` section to any bundle or app config file (e.g., `config/packages/spipu_configuration.yaml`). See [Defining Configurations](./definitions.md).

### 5. Define scopes (optional)

If your application uses scoped configurations (multi-site, multi-language, etc.), implement `ScopeListInterface` and override the `spipu.configuration.service.scope_list` service. See [Defining Configurations](./definitions.md).

## Admin UI

The admin interface is available at `/configuration/list`. It requires the role `ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW` to view and `ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT` to edit. The combined role `ROLE_ADMIN_MANAGE_CONFIGURATION` grants both and is itself granted to `ROLE_SUPER_ADMIN`.

[back](./README.md)
