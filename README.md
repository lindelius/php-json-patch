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
    { "op": "add", "path": "/name", "value": "Darth Vader" },
    { "op": "copy", "from": "/friends", "path": "/enemies" },
    { "op": "move", "from": "/name", "path": "/title" },
    { "op": "remove", "path": "/friends" },
    { "op": "replace", "path": "/order", "value": "Sith" },
    { "op": "test", "path": "/title", "value": "Darth Vader" }
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

You can atomically apply the patches through one of the [`Lindelius\JsonPatch\PatcherInterface`](src/PatcherInterface.php) methods...

```php
// Option 1: Provide the raw JSON string
$newDocument = $patcher->patchFromJson($documentAsArray, $operationsAsJson);

// Option 2: Provide the JSON Patch operations in array format
$newDocument = $patcher->patch($documentAsArray, $operationsAsArray);
```

And get a new document back.

```json
{
    "name": "Darth Vader",
    "enemies": ["Obi-Wan Kenobi", "Ahsoka Tano"],
    "order": "Sith"
}
```

Please note that this library only supports working with array documents, which means that if you would like to patch entity models (or other objects) you must first convert them to array format before applying the patches.

### Protected Paths

This library has built-in support for registering "protected paths", which are paths that may not be modified by any patch operation. Protected paths will also block modifications to their parents and all of their children.

For example, by protecting a path, `/some/protected/path`...

```php
$patcher->addProtectedPath("/some/protected/path");
```

The following paths would be protected:

- `/` - The root of the document
- `/some` - The top-most parent
- `/some/protected` - The immediate parent
- `/some/protected/path` - The actual path
- `/some/protected/path/child` - Any immediate or nested children

Please note that "test" operations can still operate on protected paths since they are not actually modifying the document.
