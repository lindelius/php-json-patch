# php-json-patch

[![CircleCI](https://circleci.com/gh/lindelius/php-json-patch.svg?style=shield)](https://circleci.com/gh/lindelius/php-json-patch)
[![Coverage Status](https://coveralls.io/repos/github/lindelius/php-json-patch/badge.svg?branch=master)](https://coveralls.io/github/lindelius/php-json-patch?branch=master)

A zero-dependency PHP implementation of JSON Patch ([RFC 6902](https://tools.ietf.org/html/rfc6902)).

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Protected Paths](#protected-paths)

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
    { "op": "add", "path": "/name", "value": "Darth Vader" },
    { "op": "copy", "from": "/friends", "path": "/enemies" },
    { "op": "move", "from": "/name", "path": "/title" },
    { "op": "remove", "path": "/friends" },
    { "op": "replace", "path": "/order", "value": "Sith" },
    { "op": "test", "path": "/title", "value": "Darth Vader" }
]
```

Apply them to a given document through one of the [PatcherInterface](src/PatcherInterface.php) methods.

```php
$document = [
    "name" => "Anakin Skywalker",
    "friends" => ["Obi-Wan Kenobi", "Ahsoka Tano"],
    "order" => "Jedi",
];

// Option 1: Provide the raw JSON string
$newDocument = $patcher->patchFromJson($document, $json);

// Option 2: Provide the JSON Patch operations in array format
$newDocument = $patcher->patch($document, json_decode($json, true));

// array(3) {
//   ["title"]=>
//   string(11) "Darth Vader"
//   ["enemies"]=>
//   array(2) {
//     [0]=>
//     string(14) "Obi-Wan Kenobi"
//     [1]=>
//     string(11) "Ahsoka Tano"
//   }
//   ["order"]=>
//   string(4) "Sith"
// }
```

Please note that this library only supports working with array documents, which means that if you would like to patch entity models you must first convert them to array format before applying the patches.

### Protected Paths

This library has built-in support for registering "protected paths", which are paths that may not be modified by any patch operation. A protected path will also block modifications to its parents and all of its children.

The protected path below would, for example, also block direct modifications to `/`, `/some`, `/some/protected`, and `/some/protected/path/and/child`.

```php
$patcher->addProtectedPath("/some/protected/path");
```

If a patch operation attempts to modify a protected path, a [ProtectedPathException](src/Exception/ProtectedPathException.php) exception will be thrown.
