# Laravel Preflight Checks

![Laravel Supported Versions](https://img.shields.io/badge/laravel-6.x/7.x/8.x-green.svg)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Performs pre-flight checks to ensure configuration and setup for deployment or development.

This package is particularly useful for automated deployments where configuration is managed separately (such as containerized deployments via Docker, K8s, etc). It can also be used as a go/no-go check for setting up local and/or dev environments.

## Installation

You can install the package via composer:

```bash
composer require kirschbaum-development/laravel-preflight-checks
```

## Setup

After requiring the composer package, publish the config file

```bash
php artisan vendor:publish --provider="Kirschbaum\PreflightChecks\PreflightChecksServiceProvider"
```

Configure the `config/preflight_checks.php` file with the configuration necessary for your app. Some defaults are provided (commented out) based on typical environments.

The config file is structured like so: `'checks'` > `environment name` > `array of checks`

```php
return [
    'checks' => [
        'production' => [
            // Database::class,
            // Redis::class,
            // Configuration::class => [
            //     // Essential production keys here
            // ],
        ],

        'anyEnvironmentName' => [
            // Any class(es) extending Kirschbaum\PreflightChecks\Checks\PreflightCheck::class
        ],

        // ...
    ],
];
```

Every check can be specified with options, for example:

```php
'production' => [
    Database::class => [
        'connection' => 'db2'
    ],
]
```

Or with the fully explicit syntax:

```php
'production' => [
    [
        'check' => Database::class,
        'options' => [
            'connection' => 'db2'
        ]
    ],
]
```

If you need to repeat checks (for example, when using multiple database connections), you will need to use the full syntax.

## Available Checks

### Database

`Kirschbaum\PreflightChecks\Checks\Database`

Checks that the database connection can be established, via the PDO, and that the required config keys are set. It outputs some server info and version information.

| Option | Description |
| --- | --- |
| `connection` | The name of the connection in `config/database.php` |

### Redis

`Kirschbaum\PreflightChecks\Checks\Redis`

Checks that Redis connection can be established, and that the required config keys are set.

(No options)

### Configuration

`Kirschbaum\PreflightChecks\Checks\Configuration`

Checks that the specified config keys are set. This checks the `config` values, NOT the `env` values to ensure that in higher environments the correct detection is taking place. As such, make sure to specify the same keys you'd use for `config(...)`.

The accepted options for the `Configuration` preflight check is a list of config keys to check. For example:

```php
'production' => [
    Configuration::class => [
        'services.payment.key',
        'services.mail.key',
        // ...
    ]
]
```

### Write Your Own!

If you have a special startup consideration you'd like to make, feel free write your own check, extending `Kirschbaum\PreflightChecks\Checks\PreflightCheck`.

Specify any necessary config keys on the `$requiredConfig` property.

Implement the `check` method, which should perform your check and mark the `$result` as pass/fail.

Example:

```php
/**
 * Performs the preflight check.
 *
 * This method should set a pass/fail on the result.
 */
public function check(Result $result): Result
{
    try {
        // Check something is up/ready/willing/etc
    } catch (Exception $e) {
        return $result->fail($e->getMessage(), $e);
    }

    return $result->pass('Woohoo! It didn\'t fail!', $dataFromTheCheck);
}
```

## Usage

Basic usage is via the Artisan command:

```bash
php artisan preflight:check
```

If you would like to see the info on successful checks (not just the failures), add a verbose flag `-v`.

You may test other environment by specifying the artisan environment (`--env`).

For higher and/or automated environments (such as CI/CD), you may want to use the `--show-only-failures` flag to cut down on noise.

### Kubernetes

In Kubernetes deployments, this can be used in a startup probe (1.20+):

```yaml
startupProbe:
  exec:
    command:
    - php
    - artisan
    - preflight:check
    - --show-only-failures
  failureThreshold: 30
  periodSeconds: 10
```

You could also set this up for a readiness probe, but keep in mind that probe is still run throughout the entire lifecycle of the container (we are establishing connections to Redis or the DB, which are not insignificant to consider).

### Containerized Environments

In containerized environments (including K8s), you may want to "block" container startup (e.g. `php-fpm`) with this command, to ensure the correct environment was loaded and cached properly. For the standard `php-fpm` docker container, you can use a startup script such as:

```bash
#!/bin/bash
set -e

php artisan optimize
php artisan preflight:check -v
php-fpm
```

### Local Environment

The `preflight:check` command can also provide a concrete method of assuring all the appropriate environment configuration has taken place. This can be especially helpful when bringing on new developers, as simply running `php artisan preflight:check` can give them a good indication of what's left to setup/configure before their environment is live.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email zack@kirschbaumdevelopment.com or nathan@kirschbaumdevelopment.com instead of using the issue tracker.

## Credits

- [Zack Teska](https://github.com/zerodahero)

## Sponsorship

Development of this package is sponsored by Kirschbaum Development Group, a developer driven company focused on problem solving, team building, and community. Learn more [about us](https://kirschbaumdevelopment.com) or [join us](https://careers.kirschbaumdevelopment.com)!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
