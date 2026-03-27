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
 * Chained serializer for applying multiple serialization operations in sequence.
 *
 * @package  Serialize
 * @category Horde
 */
class ChainedSerializer implements SerializerInterface
{
    /**
     * @param SerializerInterface[] $serializers Array of serializers to apply in order
     */
    public function __construct(
        private readonly array $serializers,
    ) {
        if (empty($this->serializers)) {
            throw new Exception('ChainedSerializer requires at least one serializer');
        }

        foreach ($this->serializers as $serializer) {
            if (!$serializer instanceof SerializerInterface) {
                throw new Exception('All items must implement SerializerInterface');
            }
        }
    }

    public function serialize(mixed $data): string
    {
        $result = $data;

        foreach ($this->serializers as $serializer) {
            $result = $serializer->serialize($result);
        }

        if (!is_string($result)) {
            throw new Exception('Final serialization result must be a string');
        }

        return $result;
    }

    public function unserialize(string $data): mixed
    {
        $result = $data;

        // Apply unserializers in reverse order
        foreach (array_reverse($this->serializers) as $serializer) {
            $result = $serializer->unserialize($result);
        }

        return $result;
    }

    public function isSupported(): bool
    {
        foreach ($this->serializers as $serializer) {
            if (!$serializer->isSupported()) {
                return false;
            }
        }

        return true;
    }
}
