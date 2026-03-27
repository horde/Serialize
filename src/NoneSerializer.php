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
 * No-operation serializer (passthrough).
 *
 * @package  Serialize
 * @category Horde
 */
class NoneSerializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        if (!is_string($data)) {
            throw new Exception('None serializer requires string input for passthrough');
        }

        return $data;
    }

    public function unserialize(string $data): string
    {
        return $data;
    }

    public function isSupported(): bool
    {
        return true;
    }
}
