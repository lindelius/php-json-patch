<?php

namespace Lindelius\JsonPatch\Exception;

use RuntimeException;

class InvalidOperationException extends RuntimeException implements PatchException
{
}
