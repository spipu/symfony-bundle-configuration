# Using Configurations

[back](./README.md)

## Reading Values

Inject `ConfigurationManager` and call `get()`:

```php
use Spipu\ConfigurationBundle\Service\ConfigurationManager;

class MyService
{
    public function __construct(private ConfigurationManager $config) {}

    public function doSomething(): void
    {
        $siteUrl = $this->config->get('app.website.url');
        $sender  = $this->config->get('app.email.sender');
    }
}
```

### Reading a scoped value

Pass the scope code as the second argument. The bundle falls back to the global value, then the default, if no scope-specific value exists:

```php
// Read the 'fr' scope override, falling back to the global value
$frUrl = $this->config->get('app.website.url', 'fr');
```

Passing `null`, `''`, or `'global'` all resolve to the global scope.

### Reading an encrypted value

Use `getEncrypted()` to automatically decrypt the stored ciphertext:

```php
$apiSecret = $this->config->getEncrypted('app.api.secret');
```

### Reading a file path or URL

```php
// Absolute filesystem path to the file, or null if not set
$path = $this->config->getFilePath('app.logo');

// Public URL to the file, or null if not set
$url = $this->config->getFileUrl('app.logo');
```

### Verifying a password value

Use `isPasswordValid()` to verify a plaintext password against the stored hash:

```php
if ($this->config->isPasswordValid('app.admin.password', $submittedPassword)) {
    // Password is correct
}
```

## Writing Values

```php
// Set a standard value (global scope)
$this->config->set('app.website.url', 'https://example.com');

// Set a scoped value
$this->config->set('app.website.url', 'https://fr.example.com', 'fr');

// Set an encrypted value (automatically encrypts before storing)
$this->config->setEncrypted('app.api.secret', $plainSecret);

// Set a password value (automatically hashes before storing)
$this->config->setPassword('app.admin.password', $newPassword);

// Upload a file (pass a Symfony UploadedFile)
$this->config->setFile('app.logo', $uploadedFile);
$this->config->setFile('app.logo', null); // remove the file
```

## Deleting Values

Deleting a stored value causes subsequent reads to fall back to the default:

```php
$this->config->delete('app.website.url');          // delete global value
$this->config->delete('app.website.url', 'fr');    // delete scope-specific value
```

## Clearing the Cache

Values are cached in the Symfony cache pool for 24 hours. Flush the cache after bulk changes:

```php
$this->config->clearCache();
```

## BasicConfigurationManager

`BasicConfigurationManager` is the parent class of `ConfigurationManager`. It exposes the following methods and is suitable for injection in read-heavy services that do not need password/encrypted/file handling:

| Method | Description |
|--------|-------------|
| `getDefinitions(): Definition[]` | Return all registered definitions |
| `getDefinition(string $key): Definition` | Return a single definition |
| `getField(string $key): FieldInterface` | Return the field object for a key |
| `getAll(): array` | Return all current values grouped by scope |
| `get(string $key, ?string $scope): mixed` | Read a value |
| `set(string $key, mixed $value, ?string $scope): void` | Write a value |
| `delete(string $key, ?string $scope): void` | Delete a stored value |
| `clearCache(): void` | Flush the value cache |

`ConfigurationManager` extends `BasicConfigurationManager` and adds:

| Method | Description |
|--------|-------------|
| `isPasswordValid(string $key, string $raw, ?string $scope): bool` | Verify a plaintext password |
| `setPassword(string $key, ?string $value, ?string $scope): void` | Hash and store a password |
| `getEncrypted(string $key, ?string $scope): ?string` | Decrypt and return an encrypted value |
| `setEncrypted(string $key, ?string $value, ?string $scope): void` | Encrypt and store a value |
| `setFile(string $key, ?UploadedFile $file, ?string $scope): void` | Save or remove an uploaded file |
| `getFilePath(string $key, ?string $scope): ?string` | Return the absolute file path |
| `getFileUrl(string $key, ?string $scope): ?string` | Return the public file URL |

## Events

Every time a value is written (`set`) or deleted (`delete`), two events are dispatched using `EventDispatcherInterface`:

| Event name | When |
|------------|------|
| `spipu.configuration.all` | Always, for any key |
| `spipu.configuration.<key>` | For the specific key (e.g., `spipu.configuration.app.website.url`) |

Both events are instances of `Spipu\ConfigurationBundle\Event\ConfigurationEvent`, which exposes:

```php
$event->getConfigDefinition(); // Definition object for the changed key
$event->getScope();            // Scope code (null = global)
```

## Twig Filters

The bundle registers a Twig extension with two filters:

```twig
{# Read a configuration value #}
{{ 'app.website.url' | get_config }}

{# Read a scoped value #}
{{ 'app.website.url' | get_config('fr') }}

{# Get the public URL of a file configuration #}
{{ 'app.logo' | get_config_file_url }}
```

## Console Commands

| Command | Description |
|---------|-------------|
| `spipu:configuration:show` | Display all values or a single key |
| `spipu:configuration:edit` | Set the value of a key |
| `spipu:configuration:delete` | Delete a stored value (restores the default) |
| `spipu:configuration:clear-cache` | Flush the configuration value cache |
| `spipu:configuration:scope` | List all registered scopes |

### spipu:configuration:show

```
Options:
  -k, --key=KEY     Key to display (if omitted, all keys are shown)
  -s, --scope=SCOPE Scope code (default: global)
  -d, --direct      Output only the raw value (for scripting)
```

Example:

```bash
php bin/console spipu:configuration:show --key=app.website.url
php bin/console spipu:configuration:show --key=app.website.url --scope=fr --direct
```

### spipu:configuration:edit

File-type keys cannot be set via this command.

```
Options:
  --key=KEY     Key to edit (required)
  --value=VALUE New value (required)
  --scope=SCOPE Scope code (default: global)
```

### spipu:configuration:delete

```
Options:
  --key=KEY     Key to delete (required)
  --scope=SCOPE Scope code (default: global)
```

## Admin UI

The admin interface is available at `/configuration/list/` (optionally followed by a scope code, e.g. `/configuration/list/fr`). It requires:

- `ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW` to view the list
- `ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT` to edit a value

The combined role `ROLE_ADMIN_MANAGE_CONFIGURATION` grants both. `ROLE_SUPER_ADMIN` inherits `ROLE_ADMIN_MANAGE_CONFIGURATION` automatically.

The list groups keys by their main category (the first dot-separated segment of the key code). When scopes are configured, a scope selector is shown at the top.

[back](./README.md)
