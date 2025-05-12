# Mailcheck PHP
**Advanced email pattern validation** ✉️

![Mailcheck Banner](https://via.placeholder.com/800x200?text=Mailcheck+PHP)

> Specialized library for validating standard email formats with advanced provider filtering and validation features. 🔍

[![GitHub](https://img.shields.io/badge/GitHub-Repository-blue.svg)](https://github.com/pedrohrigolin/Mailcheck-PHP)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/Version-1.0.0-green.svg)](https://github.com/pedrohrigolin/Mailcheck-PHP/releases)

## Table of Contents

- [Introduction](#introduction)
- [Key Features](#key-features)
- [Installation](#installation)
- [Core Concepts](#core-concepts)
  - [Validation Modes](#validation-modes)
  - [Provider Validation](#provider-validation)
  - [Extension Validation](#extension-validation)
- [Usage Guide](#usage-guide)
  - [Basic Validation](#basic-validation)
  - [Provider Validation](#provider-validation-1)
  - [Extension Validation](#extension-validation-1)
  - [Email Sanitization](#email-sanitization)
  - [Batch Processing](#batch-processing)
- [API Reference](#api-reference)
  - [Validation Methods](#validation-methods)
  - [Sanitization Methods](#sanitization-methods)
  - [Provider Management](#provider-management)
  - [Extension Management](#extension-management)
  - [Helper Methods](#helper-methods)
- [Advanced Examples](#advanced-examples)
- [Comparison with Other Solutions](#comparison-with-other-solutions)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Contribution and Support](#contribution-and-support)
- [License](#license)

## Introduction

**Mailcheck** is a PHP library specialized in validating standard email formats, designed to go beyond the basic validation provided by native functions such as `filter_var()`. Developed for use cases that require strict validation of common email patterns, such as web forms, data cleaning pipelines, database filtering, and allowlist-based security systems.

The library allows you to validate email addresses based on three main criteria:
- **Standard format** - checks if the email follows the basic syntax
- **Allowed providers** - checks if the domain is in a list of trusted providers
- **Domain extensions** - checks if the email ends with an allowed extension (such as .com, .com.br)

Unlike generic validators, Mailcheck is deliberately restrictive and configurable, allowing you to define exactly which types of email addresses are valid for your specific use case.

## Key Features

- **More restrictive validation** than `filter_var()` for common use cases ✅
- **Configurable allowlists** for providers and domain extensions 📋
- **Dual mode operation** - static and instance methods for different code styles 🔄
- **Comprehensive sanitization utilities** for input normalization 🧹
- **Batch processing capability** for validating multiple emails 📦
- **Complete customization** of validation rules to meet specific requirements 🔧
- **Unicode support** for international emails with special characters 🌐
- **Optimized performance** using compiled regular expressions ⚡

## Installation

### Requirements

- PHP 8.0 or higher

### Via Composer

```bash
composer require pedrohrigolin/mailcheck
```

### Manual Installation

1. Download the `Mailcheck.php` file from the [GitHub repository](https://github.com/pedrohrigolin/Mailcheck-PHP)
2. Include the file in your project:

```php
require_once 'path/to/Mailcheck.php';
```

### Integration with CodeIgniter 3

To use as a library in CodeIgniter 3, uncomment the line:

```php
// Remove the comment from this line
// defined('BASEPATH') OR exit('No direct script access allowed');
```

Then place the file in the `application/libraries/` folder of your CodeIgniter project.

## Core Concepts

### Validation Modes

Mailcheck has three main validation modes, which allow for increasing levels of verification:

#### 1. Default Mode (`MODE_DEFAULT` = 0)
- Validates only the basic email format
- Checks if the email follows the pattern `user@domain.extension`
- Similar to a basic syntax check, but more rigorous

```php
// Validation in default mode
Mailcheck::checkStatic('user@domain.com'); // true for valid format
```

#### 2. Extension Mode (`MODE_TERMINATION` = 1)
- Validates the format and checks if the email ends with one of the allowed extensions
- By default, checks extensions like `.com`, `.com.br`, `.br`
- Useful for restricting emails to specific domain extensions

```php
// Extension validation
Mailcheck::checkStatic('user@domain.com', Mailcheck::MODE_TERMINATION);
```

#### 3. Provider Mode (`MODE_PROVIDERS` = 2)
- Validates the format and checks if the email uses one of the allowed providers
- By default, checks common providers like gmail.com, outlook.com, hotmail.com, etc.
- Ideal for applications that accept only emails from trusted providers

```php
// Provider validation
Mailcheck::checkStatic('user@gmail.com', Mailcheck::MODE_PROVIDERS);
```

### Provider Validation

Provider validation checks if the email domain is in the list of allowed providers. By default, this list includes common providers such as:

- gmail.com
- outlook.com/outlook.com.br
- hotmail.com/hotmail.com.br
- yahoo.com/yahoo.com.br
- live.com/live.com.br
- terra.com/terra.com.br
- icloud.com
- uol.com.br
- And others...

This list can be modified using the methods:
- `add_providers()` / `add_providersStatic()`
- `remove_providers()` / `remove_providersStatic()`
- `replace_providers()` / `replace_providersStatic()`

### Extension Validation

Extension validation checks if the email ends with one of the allowed domain extensions. By default, it includes:

- .com
- .com.br
- .br

This list can be modified using the methods:
- `add_terminations()` / `add_terminationsStatic()`
- `remove_terminations()` / `remove_terminationsStatic()`
- `replace_terminations()` / `replace_terminationsStatic()`

## Usage Guide

### Basic Validation

Mailcheck can be used in both static mode and as an instance.

#### Static Usage

```php
// Validates a single email in default mode
if (Mailcheck::checkStatic('user@domain.com')) {
    echo 'Valid email!';
}

// Validates a single email in extension mode
if (Mailcheck::checkStatic('user@domain.com', Mailcheck::MODE_TERMINATION)) {
    echo 'Valid email with allowed extension!';
}

// Validates a single email in provider mode
if (Mailcheck::checkStatic('user@gmail.com', Mailcheck::MODE_PROVIDERS)) {
    echo 'Valid email from an allowed provider!';
}

// You can also specify the mode as a string
if (Mailcheck::checkStatic('user@gmail.com', 'MODE_PROVIDERS')) {
    echo 'Valid email from an allowed provider!';
}
```

#### Instance Usage

```php
// Creates an instance of the class
$mailcheck = new Mailcheck();

// Validates a single email in default mode
if ($mailcheck->check('user@domain.com')) {
    echo 'Valid email!';
}

// Validates a single email in extension mode
if ($mailcheck->check('user@domain.com', Mailcheck::MODE_TERMINATION)) {
    echo 'Valid email with allowed extension!';
}

// Validates a single email in provider mode
if ($mailcheck->check('user@gmail.com', Mailcheck::MODE_PROVIDERS)) {
    echo 'Valid email from an allowed provider!';
}
```

### Provider Validation

To validate if an email uses a specific provider, use the `MODE_PROVIDERS` mode:

```php
// Validates if the email uses an allowed provider
$email = 'user@gmail.com';
if (Mailcheck::checkStatic($email, Mailcheck::MODE_PROVIDERS)) {
    echo "The email $email uses an allowed provider!";
}
```

#### Customizing Allowed Providers

```php
// Adds new providers to the existing list
Mailcheck::add_providersStatic('mydomain.com');
// or multiple at once
Mailcheck::add_providersStatic(['mydomain.com', 'company.com.br']);

// Removes providers from the list
Mailcheck::remove_providersStatic('hotmail.com');

// Replaces the entire provider list
Mailcheck::replace_providersStatic([
    'company.com',
    'company.com.br',
    'partner.com'
]);

// Checks the current list of allowed providers
$providers = Mailcheck::getProviders();
print_r($providers);
```

### Extension Validation

To validate if an email uses a specific extension, use the `MODE_TERMINATION` mode:

```php
// Validates if the email uses an allowed extension
$email = 'user@domain.com';
if (Mailcheck::checkStatic($email, Mailcheck::MODE_TERMINATION)) {
    echo "The email $email has an allowed extension!";
}
```

#### Customizing Allowed Extensions

```php
// Adds new extensions to the existing list
Mailcheck::add_terminationsStatic('io');
// or multiple at once
Mailcheck::add_terminationsStatic(['io', 'tech'. 'io.tech']);

// Removes extensions from the list
Mailcheck::remove_terminationsStatic('br');

// Replaces the entire extension list
Mailcheck::replace_terminationsStatic([
    'co',
    'io',
    'dev'
    'co.io'
]);

// Checks the current list of allowed extensions
$extensions = Mailcheck::getTerminations();
print_r($extensions);
```

### Email Sanitization

Mailcheck provides functions to clean and normalize email addresses: 🧹

```php
// Sanitizes a single email
$clean_email = Mailcheck::sanitize(' user,,name@domain.com ');
// Result: "user.name@domain.com"

// Sanitizes and fixes multiple dots
$fixed_email = Mailcheck::sanitize('user...name@domain.com', true);
// Result: "user.name@domain.com"

// Sanitizes an array of emails
$emails = [
    ' user1@domain.com ',
    'user2,,name@domain.com',
    'user3...name@domain.com'
];
$clean_emails = Mailcheck::sanitize($emails, true);
```

#### Detailed Sanitization Examples 🔍

```php
// Example 1: Basic sanitization with spaces and commas
$dirty_email = '  john,,doe@example.com ';
$clean_email = Mailcheck::sanitize($dirty_email);
echo "Original: '$dirty_email' → Sanitized: '$clean_email'";
// Output: Original: '  john,,doe@example.com ' → Sanitized: 'john.doe@example.com'

// Example 2: Fixing multiple dots
$dirty_email = 'maria....silva@company...com.br';
$clean_email = Mailcheck::sanitize($dirty_email, true);
echo "Original: '$dirty_email' → Sanitized: '$clean_email'";
// Output: Original: 'maria....silva@company...com.br' → Sanitized: 'maria.silva@company.com.br'

// Example 3: Sanitizing special characters
$dirty_email = 'carlos!#$%^&*()_+=@gmail.com';
$clean_email = Mailcheck::sanitize($dirty_email);
echo "Original: '$dirty_email' → Sanitized: '$clean_email'";
// Output: Original: 'carlos!#$%^&*()_+=@gmail.com' → Sanitized: 'carlos@gmail.com'

// Example 4: Sanitizing multiple emails with different results
$dirty_emails = [
    ' ana..silva@hotmail.com ',
    'pedro,,santos@gmail...com',
    'jose#ribeiro@uol.com.br',
    'maria@example..com.br'
];

$clean_emails = Mailcheck::sanitize($dirty_emails, true);

echo "Batch sanitization results:\n";
foreach ($dirty_emails as $i => $original) {
    echo "Original: '$original' → Sanitized: '{$clean_emails[$i]}'\n";
}

// Output:
// Batch sanitization results:
// Original: ' ana..silva@hotmail.com ' → Sanitized: 'ana.silva@hotmail.com'
// Original: 'pedro,,santos@gmail...com' → Sanitized: 'pedro.santos@gmail.com'
// Original: 'jose#ribeiro@uol.com.br' → Sanitized: 'joseribeiro@uol.com.br'
// Original: 'maria@example..com.br' → Sanitized: 'maria@example.com.br'
```

### Batch Processing

Mailcheck allows you to validate and filter multiple emails at once:

#### Batch Validation

```php
// Checks if all emails in the array are valid
$emails = [
    'user1@gmail.com',
    'user2@outlook.com', 
    'user3@yahoo.com'
];

if (Mailcheck::checkStatic($emails, Mailcheck::MODE_PROVIDERS)) {
    echo "All emails are valid and use allowed providers!";
} else {
    echo "At least one of the emails is invalid or uses a non-allowed provider!";
}
```

#### Email Filtering

```php
// List of emails to filter
$emails = [
    'valid@gmail.com',
    'invalid@unknown.domain',
    'another@hotmail.com',
    'test@nonexistent.provider'
];

// Filters to keep only valid emails (whitelist mode = true)
$valid_emails = Mailcheck::filterStatic($emails, Mailcheck::MODE_PROVIDERS);

// Filters to keep only invalid emails (whitelist mode = false)
$invalid_emails = Mailcheck::filterStatic($emails, Mailcheck::MODE_PROVIDERS, false);

// Using the instance version
$mailcheck = new Mailcheck();
$valid = $mailcheck->filter($emails, Mailcheck::MODE_PROVIDERS);
```

#### Advanced Filtering Examples with Results 🧩

```php
// Diverse email list for demonstration
$mixed_emails = [
    'maria@gmail.com',
    'joao@outlook.com',
    'roberto@invalid-domain.xyz',
    'laura@hotmail.com',
    'carlos@test-server.io',
    'antonio@yahoo.com',
    'invalid.email.com',  // Missing @
    'test@gmail.com',
    '@domain.com',        // Missing local part
    'ana@uol.com.br'
];

// Example 1: Basic format filter
$valid_format = Mailcheck::filterStatic($mixed_emails, Mailcheck::MODE_DEFAULT);
echo "✅ Emails with valid format (" . count($valid_format) . "):\n";
print_r($valid_format);
// Output:
// ✅ Emails with valid format (8):
// Array
// (
//     [0] => maria@gmail.com
//     [1] => joao@outlook.com
//     [2] => roberto@invalid-domain.xyz
//     [3] => laura@hotmail.com
//     [4] => carlos@test-server.io
//     [5] => antonio@yahoo.com
//     [6] => test@gmail.com
//     [7] => ana@uol.com.br
// )

// Example 2: Filter by allowed provider
$valid_providers = Mailcheck::filterStatic($mixed_emails, Mailcheck::MODE_PROVIDERS);
echo "✅ Emails from allowed providers (" . count($valid_providers) . "):\n";
print_r($valid_providers);
// Output:
// ✅ Emails from allowed providers (6):
// Array
// (
//     [0] => maria@gmail.com
//     [1] => joao@outlook.com
//     [2] => laura@hotmail.com
//     [3] => antonio@yahoo.com
//     [4] => test@gmail.com
//     [5] => ana@uol.com.br
// )

// Example 3: Filter by extension
$valid_extensions = Mailcheck::filterStatic($mixed_emails, Mailcheck::MODE_TERMINATION);
echo "✅ Emails with allowed extensions (" . count($valid_extensions) . "):\n";
print_r($valid_extensions);
// Output:
// ✅ Emails with allowed extensions (7):
// Array
// (
//     [0] => maria@gmail.com
//     [1] => joao@outlook.com
//     [2] => laura@hotmail.com
//     [3] => antonio@yahoo.com
//     [4] => test@gmail.com
//     [5] => ana@uol.com.br
//     [6] => @domain.com
// )

// Example 4: Inverse filter (invalid emails)
$invalid_emails = Mailcheck::filterStatic($mixed_emails, Mailcheck::MODE_PROVIDERS, false);
echo "❌ Emails from non-allowed providers (" . count($invalid_emails) . "):\n";
print_r($invalid_emails);
// Output:
// ❌ Emails from non-allowed providers (4):
// Array
// (
//     [0] => roberto@invalid-domain.xyz
//     [1] => carlos@test-server.io
//     [2] => invalid.email.com
//     [3] => @domain.com
// )

// Example 5: Combination of sanitization and filtering
$emails_to_clean = [
    ' maria..silva@gmail.com ',
    'joao,,santos@outlook.com',
    'user@invalid-domain.net',
    'laura...santos@hotmail.com'
];

// First sanitize
$sanitized_emails = Mailcheck::sanitize($emails_to_clean, true);
// Then filter
$filtered_emails = Mailcheck::filterStatic($sanitized_emails, Mailcheck::MODE_PROVIDERS);

echo "🧹 Emails after sanitization and filtering (" . count($filtered_emails) . "):\n";
print_r($filtered_emails);
// Output:
// 🧹 Emails after sanitization and filtering (3):
// Array
// (
//     [0] => maria.silva@gmail.com
//     [1] => joao.santos@outlook.com
//     [2] => laura.santos@hotmail.com
// )
```

## API Reference

### Validation Methods

#### `checkStatic(string|array $email, int|string $type = MODE_DEFAULT): bool`
Validates an email or array of emails in static mode.

- **Parameters:**
  - `$email`: String with the email or array of emails to validate
  - `$type`: Validation mode (0/MODE_DEFAULT, 1/MODE_TERMINATION, 2/MODE_PROVIDERS)
- **Return:** Boolean - true if valid, false if invalid
- **Example:**
  ```php
  $is_valid = Mailcheck::checkStatic('user@gmail.com', Mailcheck::MODE_PROVIDERS);
  ```

#### `check(string|array $email, int|string $type = MODE_DEFAULT): bool`
Validates an email or array of emails using an instance.

- **Parameters:**
  - `$email`: String with the email or array of emails to validate
  - `$type`: Validation mode (0/MODE_DEFAULT, 1/MODE_TERMINATION, 2/MODE_PROVIDERS)
- **Return:** Boolean - true if valid, false if invalid
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $is_valid = $mailcheck->check('user@gmail.com', Mailcheck::MODE_PROVIDERS);
  ```

#### `filterStatic(array $email, int|string $type = MODE_DEFAULT, bool $white_list = true): array`
Filters an array of emails keeping or removing the valid ones (static mode).

- **Parameters:**
  - `$email`: Array of emails to filter
  - `$type`: Validation mode (0/MODE_DEFAULT, 1/MODE_TERMINATION, 2/MODE_PROVIDERS)
  - `$white_list`: true to keep valid ones, false to keep invalid ones
- **Return:** Array of filtered emails
- **Example:**
  ```php
  $valid_emails = Mailcheck::filterStatic($emails, Mailcheck::MODE_PROVIDERS);
  ```

#### `filter(array $email, int|string $type = MODE_DEFAULT, bool $white_list = true): array`
Filters an array of emails keeping or removing the valid ones (instance mode).

- **Parameters:**
  - `$email`: Array of emails to filter
  - `$type`: Validation mode (0/MODE_DEFAULT, 1/MODE_TERMINATION, 2/MODE_PROVIDERS)
  - `$white_list`: true to keep valid ones, false to keep invalid ones
- **Return:** Array of filtered emails
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $valid_emails = $mailcheck->filter($emails, Mailcheck::MODE_PROVIDERS);
  ```

### Sanitization Methods

#### `sanitize(string|array $email, bool $fixDots = false): string|array`
Normalizes the email format(s), removing spaces, replacing commas with dots, etc.

- **Parameters:**
  - `$email`: String with the email or array of emails to sanitize
  - `$fixDots`: If true, fixes multiple dots (e.g., `user...name@domain.com` → `user.name@domain.com`)
- **Return:** String or array with sanitized email(s)
- **Example:**
  ```php
  $clean_email = Mailcheck::sanitize(' user,,name@domain.com ');
  // Result: "user.name@domain.com"
  ```

### Provider Management

#### `add_providersStatic(string|array $providers): void`
Adds one or more providers to the allowlist (static mode).

- **Parameters:**
  - `$providers`: String with one provider or array of providers to add
- **Example:**
  ```php
  Mailcheck::add_providersStatic('mydomain.com');
  // or
  Mailcheck::add_providersStatic(['mydomain.com', 'company.com.br']);
  ```

#### `add_providers(string|array $providers): void`
Adds one or more providers to the allowlist (instance mode).

- **Parameters:**
  - `$providers`: String with one provider or array of providers to add
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->add_providers('mydomain.com');
  ```

#### `remove_providersStatic(string|array $providers): void`
Removes one or more providers from the allowlist (static mode).

- **Parameters:**
  - `$providers`: String with one provider or array of providers to remove
- **Example:**
  ```php
  Mailcheck::remove_providersStatic('hotmail.com');
  // or
  Mailcheck::remove_providersStatic(['hotmail.com', 'outlook.com']);
  ```

#### `remove_providers(string|array $providers): void`
Removes one or more providers from the allowlist (instance mode).

- **Parameters:**
  - `$providers`: String with one provider or array of providers to remove
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->remove_providers('hotmail.com');
  ```

#### `replace_providersStatic(array $providers): void`
Replaces the entire list of allowed providers (static mode).

- **Parameters:**
  - `$providers`: Array with the new allowed providers
- **Example:**
  ```php
  Mailcheck::replace_providersStatic(['company.com', 'company.com.br']);
  ```

#### `replace_providers(array $providers): void`
Replaces the entire list of allowed providers (instance mode).

- **Parameters:**
  - `$providers`: Array with the new allowed providers
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->replace_providers(['company.com', 'company.com.br']);
  ```

### Extension Management

#### `add_terminationsStatic(string|array $terminations): void`
Adds one or more extensions to the allowlist (static mode).

- **Parameters:**
  - `$terminations`: String with one extension or array of extensions to add
- **Example:**
  ```php
  Mailcheck::add_terminationsStatic('io');
  // or
  Mailcheck::add_terminationsStatic(['io', 'tech']);
  ```

#### `add_terminations(string|array $terminations): void`
Adds one or more extensions to the allowlist (instance mode).

- **Parameters:**
  - `$terminations`: String with one extension or array of extensions to add
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->add_terminations('io');
  ```

#### `remove_terminationsStatic(string|array $terminations): void`
Removes one or more extensions from the allowlist (static mode).

- **Parameters:**
  - `$terminations`: String with one extension or array of extensions to remove
- **Example:**
  ```php
  Mailcheck::remove_terminationsStatic('br');
  // or
  Mailcheck::remove_terminationsStatic(['br', 'com.br']);
  ```

#### `remove_terminations(string|array $terminations): void`
Removes one or more extensions from the allowlist (instance mode).

- **Parameters:**
  - `$terminations`: String with one extension or array of extensions to remove
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->remove_terminations('br');
  ```

#### `replace_terminationsStatic(array $terminations): void`
Replaces the entire list of allowed extensions (static mode).

- **Parameters:**
  - `$terminations`: Array with the new allowed extensions
- **Example:**
  ```php
  Mailcheck::replace_terminationsStatic(['co', 'io', 'dev']);
  ```

#### `replace_terminations(array $terminations): void`
Replaces the entire list of allowed extensions (instance mode).

- **Parameters:**
  - `$terminations`: Array with the new allowed extensions
- **Example:**
  ```php
  $mailcheck = new Mailcheck();
  $mailcheck->replace_terminations(['co', 'io', 'dev']);
  ```

### Helper Methods

#### `getProviders(): array`
Returns the current list of allowed providers.

- **Return:** Array with the allowed providers
- **Example:**
  ```php
  $providers = Mailcheck::getProviders();
  print_r($providers);
  ```

#### `getTerminations(): array`
Returns the current list of allowed extensions.

- **Return:** Array with the allowed extensions
- **Example:**
  ```php
  $terminations = Mailcheck::getTerminations();
  print_r($terminations);
  ```

#### `getModes(): array`
Returns the available validation mode constants.

- **Return:** Array with the available validation modes
- **Example:**
  ```php
  $modes = Mailcheck::getModes();
  print_r($modes);
  ```

## Advanced Examples

### Contact Form Integration

```php
// Processes contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Sanitizes and validates the email
    $email = Mailcheck::sanitize($email, true);
    
    if (Mailcheck::checkStatic($email, Mailcheck::MODE_PROVIDERS)) {
        // Valid email from an allowed provider
        send_email($email);
        echo "Form submitted successfully!";
    } else {
        echo "Please provide a valid email address from a trusted provider.";
    }
}
```

### Custom Registration System

```php
class UserRegistration {
    private $mailcheck;
    
    public function __construct() {
        $this->mailcheck = new Mailcheck();
        
        // Configure to allow only corporate domains
        $this->mailcheck->replace_providers([
            'mycompany.com',
            'mycompany.com.br',
            'partner.com'
        ]);
    }
    
    public function validateEmail($email) {
        $email = $this->mailcheck->sanitize($email, true);
        return $this->mailcheck->check($email, Mailcheck::MODE_PROVIDERS);
    }
    
    public function register($name, $email, $password) {
        if (!$this->validateEmail($email)) {
            return "Invalid email. Please use your corporate email.";
        }
        
        // Continue with registration...
        return "User registered successfully!";
    }
}

// Usage
$registration = new UserRegistration();
echo $registration->register('John', 'john@mycompany.com', 'password123');
```

### Batch Import Processing

```php
function import_contacts($csv_file) {
    // Load CSV
    $contacts = [];
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $contacts[] = [
                'name' => $data[0],
                'email' => $data[1]
            ];
        }
        fclose($handle);
    }
    
    // Extract only the emails
    $emails = array_column($contacts, 'email');
    
    // Sanitize all emails
    $emails = Mailcheck::sanitize($emails, true);
    
    // Separate into valid and invalid
    $valid = Mailcheck::filterStatic($emails, Mailcheck::MODE_DEFAULT);
    $invalid = Mailcheck::filterStatic($emails, Mailcheck::MODE_DEFAULT, false);
    
    // Separate by specific providers
    Mailcheck::replace_providersStatic(['gmail.com', 'hotmail.com']);
    $gmail_hotmail = Mailcheck::filterStatic($valid, Mailcheck::MODE_PROVIDERS);
    $other_providers = Mailcheck::filterStatic($valid, Mailcheck::MODE_PROVIDERS, false);
    
    return [
        'total' => count($emails),
        'valid' => $valid,
        'invalid' => $invalid,
        'gmail_hotmail' => $gmail_hotmail,
        'others' => $other_providers
    ];
}

// Usage
$result = import_contacts('contacts.csv');
echo "Total contacts: " . $result['total'] . "\n";
echo "Valid emails: " . count($result['valid']) . "\n";
echo "Invalid emails: " . count($result['invalid']) . "\n";
```

## Comparison with Other Solutions

| Feature | Mailcheck | filter_var | Simple regex validation |
|----------------|-----------|------------|----------------------------|
| Basic validation | ✅ | ✅ | ✅ |
| Strict format validation | ✅ | ❌ | ⚠️ (limited) |
| Provider validation | ✅ | ❌ | ❌ |
| Extension validation | ✅ | ❌ | ⚠️ (possible but complex) |
| Allowlists | ✅ | ❌ | ❌ |
| Batch processing | ✅ | ⚠️ (manual) | ⚠️ (manual) |
| Sanitization | ✅ | ❌ | ❌ |
| Rule customization | ✅ | ❌ | ⚠️ (difficult to maintain) |
| Unicode support | ✅ | ⚠️ (limited) | ❌ (complex) |

## Frequently Asked Questions

### Why use Mailcheck instead of filter_var()?
`filter_var()` is a generic function for validating whether an email follows the basic format, but it doesn't provide more specific validation such as checking allowed providers or domain extensions. Mailcheck was specifically designed for more restrictive and customizable validations.

### Can I use Mailcheck to validate international emails?
Yes, Mailcheck supports Unicode characters (\p{L}) in regular expressions, allowing emails with international characters.

### How do I add a corporate provider to the allowlist?
```php
Mailcheck::add_providersStatic('mycompany.com');
// or for multiple corporate domains
Mailcheck::replace_providersStatic([
    'mycompany.com',
    'department.mycompany.com',
    'branch.mycompany.com'
]);
```

### Does Mailcheck perform DNS verification of domains?
No, Mailcheck focuses on format validation and verification against allowlists. For DNS checks that confirm the actual existence of the domain, you would need to implement an additional solution using functions like `checkdnsrr()`.

### How to handle emails that have valid formats but aren't real?
Mailcheck focuses on format validation and verification against allowlists. To verify if an email actually exists, consider:
1. Integration with email verification services
2. DNS verification to confirm MX records
3. Implementation of email confirmation (sending a link or code)

---