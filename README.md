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

Given a set of JSON Patch operations...

```json
[
    { "op": "test", "path": "/name", "value": "Anakin Skywalker" },
    { "op": "replace", "path": "/name", "value": "Darth Vader" },
    { "op": "add", "path": "/order", "value": "Sith" },
    { "op": "move", "from": "/friends", "path": "/foes" },
    { "op": "remove", "path": "/friends" }
]
```

And a document...

```json
{
    "name": "Anakin Skywalker",
    "friends": ["Obi-Wan Kenobi", "Ahsoka Tano"],
    "order": "Jedi"
}
```

You can (atomically) apply the patches through one of the [`PatcherInterface`](src/PatcherInterface.php) methods...

```php
// Option 1: Provide the raw JSON string
$newDocument = $patcher->patchFromJson($documentAsArray, $operationsAsJson);

// Option 2: Provide the JSON Patch operations in array format
$newDocument = $patcher->patch($documentAsArray, $operationsAsArray);
```

And get a new document back :)

```json
{
    "name": "Darth Vader",
    "foes": ["Obi-Wan Kenobi", "Ahsoka Tano"],
    "order": "Sith"
}
```

### Protected Paths

This library has built-in support for registering "protected paths", which are paths that may not be modified by any patch operation. Protected paths will indirectly also block modifications to their parent path(s) and any child paths.

```php
$patcher->addProtectedPath("/id");
$patcher->addProtectedPath("/created_at");
$patcher->addProtectedPath("/some/nested/path");
```

Please note that "test" operations can still operate on a protected path since they are not actually modifying the document.
