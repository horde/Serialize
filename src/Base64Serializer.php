<?php

declare(strict_types=1);

/**
 * Copyright 2001-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package  Serialize
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Serialize;

/**
 * Base64 encoding implementation.
 *
 * @package  Serialize
 * @category Horde
 */
class Base64Serializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        if (!is_string($data)) {
            throw new Exception('Base64 serializer requires string input');
        }

        return base64_encode($data);
    }

    public function unserialize(string $data): string
    {
        $result = base64_decode($data, true);

        if ($result === false) {
            throw new Exception('Base64 decoding failed');
        }

        return $result;
    }

    public function isSupported(): bool
    {
        return true;
    }
}
