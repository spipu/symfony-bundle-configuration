# Defining Configuration Keys

[back](./README.md)

## Overview

Configuration keys are declared in YAML under the `spipu_configuration:` extension key. The bundle merges declarations from all loaded config files. There is no PHP interface to implement for this — definitions are pure configuration.

## Declaring Keys in YAML

Create a file such as `config/packages/spipu_configuration.yaml` (or inside any bundle's config). Each entry under `spipu_configuration:` is a configuration key:

```yaml
spipu_configuration:
    app.website.url:
        type:     url
        required: true
        default:  "https://my-website.fr"

    app.email.sender:
        type:     email
        required: true
        default:  "no-reply@my-website.fr"

    app.items.per_page:
        type:     integer
        required: false
        default:  "20"
        help:     "Number of items per page in lists."

    app.maintenance:
        type:     boolean
        required: false
        default:  "0"

    app.mode:
        type:     select
        options:  "App\\Form\\Options\\ModeOptions"
        required: true
        default:  "prod"

    app.api.secret:
        type:     encrypted
        required: true
        help:     "Third-party API secret key (stored encrypted)."

    app.admin.password:
        type:     password
        required: false

    app.logo:
        type:      file
        file_type: [png, jpeg]
        required:  false
        help:      "Site logo file."
```

### Key naming rules

Every key **must** contain at least two dot-separated parts (e.g., `app.name` is valid; `name` alone is not). The first part is the main category used to group keys in the admin UI.

## YAML Options Reference

| Option | Required | Default | Description |
|--------|----------|---------|-------------|
| `type` | yes | — | Field type (see below) |
| `required` | no | `false` | Whether an empty value is rejected |
| `default` | no | `null` | Default value (string) used when no DB value exists |
| `scoped` | no | `false` | Whether the value can be overridden per scope |
| `options` | type=`select` only | `null` | FQCN of a class providing select options |
| `unit` | no | `null` | Display unit string shown alongside the field (e.g., `"%"`) |
| `help` | no | `null` | Help text shown in the admin UI form |
| `file_type` | type=`file` only | `[]` | Allowed file extensions (e.g., `[png, jpeg]`) |

### Validation constraints

- `options` is **required** for type `select`, and **forbidden** for all other types.
- `file_type` is **only allowed** for type `file`.
- `password` and `encrypted` types **cannot have a default value**.
- For type `boolean`, the bundle automatically sets `options` to `Spipu\UiBundle\Form\Options\BooleanStatus`.

## Field Types

| Type | Description |
|------|-------------|
| `boolean` | True / false (rendered as a select with BooleanStatus options) |
| `color` | HTML color string |
| `email` | Email address (validated) |
| `encrypted` | String stored encrypted with Sodium; use `getEncrypted()` to read |
| `file` | Uploaded file; stored as a filename; use `getFilePath()` / `getFileUrl()` to read |
| `float` | Decimal number |
| `integer` | Integer number |
| `password` | Stored as a bcrypt hash; use `isPasswordValid()` / `setPassword()` |
| `select` | One value chosen from a list; requires `options` |
| `string` | Short string (single-line input) |
| `text` | Longer free-text string (textarea) |
| `url` | URL (validated) |

## Scopes

A **scope** allows overriding a configuration value for a specific context (e.g., a specific website or language). Only keys with `scoped: true` can have per-scope values.

### Implementing scopes

Create a class implementing `ScopeListInterface`:

```php
use Spipu\ConfigurationBundle\Entity\Scope;
use Spipu\ConfigurationBundle\Service\ScopeListInterface;

class MyScopeList implements ScopeListInterface
{
    /** @return Scope[] */
    public function getAll(): array
    {
        return [
            new Scope('fr', 'Français'),
            new Scope('en', 'English'),
        ];
    }
}
```

`Scope` constructor: `new Scope(string $code, string $name)`.

Scope code constraints:
- Lowercase, no HTML tags, no path-separator characters
- Not empty, not longer than 128 characters
- Not one of the reserved values: `global`, `default`, `scoped`

### Registering the scope list

Override the `spipu.configuration.service.scope_list` service alias in your application's `services.yaml`:

```yaml
# config/services.yaml
spipu.configuration.service.scope_list:
    class: App\Service\MyScopeList
```

The bundle's `ScopeList` default implementation returns an empty array (no scopes).

### Value resolution order

When reading a value with a given scope, the bundle tries each of the following in order and returns the first found:

1. The specific scope's stored value
2. The global stored value
3. The default value from the YAML definition

[back](./README.md)
