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
 * PHP native serialization implementation.
 *
 * @package  Serialize
 * @category Horde
 */
class BasicSerializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function unserialize(string $data): mixed
    {
        $result = @unserialize($data);

        // Unserialize returns false both on error and if $data is the false value
        if ($result === false && $data !== serialize(false)) {
            throw new Exception('Unserialization failed');
        }

        return $result;
    }

    public function isSupported(): bool
    {
        return true;
    }
}
