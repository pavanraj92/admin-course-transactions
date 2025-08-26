# Course Transactions & Purchase

Handles recording and management of all financial activities related to course.

## Features

- View and manage transactions
- Searchable, paginated transaction listing

## Requirements

- PHP >=8.2
- Laravel Framework >= 12.x

## Installation

### 1. Add Git Repository to `composer.json`

```json
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/pavanraj92/admin-course-transactions.git"
        }
]
```

### 2. Require the package via Composer
    ```bash
    composer require admin/course_transactions:@dev
    ```

### 3. Publish assets
    ```bash
    php artisan transactions:publish --force
    ```
---


## Usage

 **Transactions**: Track and manage course-related transactions.

## Admin Panel Routes

| Method | Endpoint                                 | Description                              |
| ------ | ---------------------------------------- | ---------------------------------------- |
| GET    | /transactions                            | List all transactions                    |
| GET    | /transactions/{transaction}              | Get transaction details                  |                    |
| GET    | /course-purchases | List all course purchases |
| GET    | /course-purchases/{id} | Show course purchases details |
---

## Protecting Admin Routes

Protect your routes using the provided middleware:

```php
Route::middleware(['web','admin.auth'])->group(function () {
    // courses transaction and purchase routes here
});
```

## License

This package is open-sourced software licensed under the MIT license.
