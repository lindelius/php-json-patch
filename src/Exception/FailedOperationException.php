<?php

namespace Lindelius\JsonPatch\Exception;

use RuntimeException;

class FailedOperationException extends RuntimeException implements PatchException
{
}
