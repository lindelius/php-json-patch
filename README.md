# php-json-patch

[![CircleCI](https://circleci.com/gh/lindelius/php-json-patch.svg?style=shield)](https://circleci.com/gh/lindelius/php-json-patch)

A zero-dependency PHP implementation of JSON Patch ([RFC 6902](https://tools.ietf.org/html/rfc6902)).

## Requirements

- PHP 7.4, or higher

## Installation

If you are using Composer, you may install the latest version of this library by running the following command from your project's root folder:

```
composer require lindelius/php-json-patch
```

You may also manually download the library by navigating to the "Releases" page and then expanding the "Assets" section
of the latest release.

## Usage

Given a set of JSON Patch operations:

```json
[
    { "op": "replace", "path": "/name", "value": "Darth Vader" },
    { "op": "replace", "path": "/order", "value": "Sith" }
]
```

You can apply them to a given document by using one of the `PatcherInterface` methods.

```php
/** @global Lindelius\JsonPatch\PatcherInterface $patcher */
/** @global string $json */

$document = [
    "name" => "Anakin Skywalker",
    "order" => "Jedi",
];

// Option 1: Provide the raw JSON string
$newDocument = $patcher->patchFromJson($document, $json);

// Option 2: Provide the JSON Patch operations in array format
$newDocument = $patcher->patch($document, json_decode($json, true));
```

Please note that this library only supports working with array documents, which means that if you would like to patch entity models you must first convert them to array format before applying the patches.

### Protected Paths

This library has built-in support for registering "protected paths", which are paths that may not be modified by any patch operation.

```php
/** @global Lindelius\JsonPatch\PatcherInterface $patcher */

$patcher->addProtectedPath("/id");
$patcher->addProtectedPath("/some/other/path");
```
