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
 * UTF-7 charset conversion serialization.
 *
 * @package  Serialize
 * @category Horde
 */
class Utf7Serializer implements SerializerInterface
{
    public function __construct(
        private readonly string $sourceCharset = 'UTF-8',
        private readonly string $targetCharset = 'UTF-8',
        private readonly bool $withBasic = false,
    ) {
    }

    public function serialize(mixed $data): string
    {
        if ($this->withBasic) {
            $data = serialize($data);
        }

        if (!is_string($data)) {
            throw new Exception('UTF-7 serializer requires string input');
        }

        return \Horde_String::convertCharset($data, $this->sourceCharset, 'UTF-7');
    }

    public function unserialize(string $data): mixed
    {
        $result = \Horde_String::convertCharset($data, 'UTF-7', $this->targetCharset);

        if ($this->withBasic) {
            $unserialized = @unserialize($result);
            if ($unserialized === false && $result !== serialize(false)) {
                throw new Exception('Unserialization failed');
            }
            return $unserialized;
        }

        return $result;
    }

    public function isSupported(): bool
    {
        return class_exists('Horde_String');
    }
}
