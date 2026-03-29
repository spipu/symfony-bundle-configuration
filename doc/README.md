# Spipu Configuration Bundle

The **ConfigurationBundle** provides a key-value application configuration system with database-backed storage, an admin UI, scoped overrides, and support for encrypted and hashed (password) values.

## Documentation

- [Installation](./install.md)
- [Defining Configurations](./definitions.md)
- [Using Configurations](./usage.md)

## Features

- **Key-value store** persisted in database, editable from the admin UI
- **Typed values**: `boolean`, `color`, `email`, `encrypted`, `file`, `float`, `integer`, `password`, `select`, `string`, `text`, `url`
- **Scoped values**: override configurations per scope (e.g., per site, per language)
- **Encrypted storage**: sensitive values are stored encrypted using Sodium (via CoreBundle)
- **Password storage**: password fields are hashed using Symfony's password hasher
- **File upload**: configurations can store uploaded file paths
- **Admin UI**: manage configuration values at `/configuration/list`
- **Console commands**: show, edit, delete, clear-cache, and scope inspection commands
- **Twig filters**: read configuration values and file URLs from templates
- **Events**: dispatched on every value change (global and per-key)
- **Required vs optional**: each configuration key can be marked required

## Requirements

- PHP 8.1+
- Symfony 6.4+
- `spipu/core-bundle`
- `spipu/ui-bundle`
- Doctrine ORM with a relational database

## Quick Start

See [Installation](./install.md) for setup steps, then [Defining Configurations](./definitions.md) to declare your configuration keys.
