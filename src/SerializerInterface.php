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
 * Interface for data serialization implementations.
 *
 * @package  Serialize
 * @category Horde
 */
interface SerializerInterface
{
    /**
     * Serialize data into a string representation.
     *
     * @param mixed $data The data to serialize
     * @return string The serialized representation
     * @throws Exception When serialization fails
     */
    public function serialize(mixed $data): string;

    /**
     * Unserialize data from a string representation.
     *
     * @param string $data The serialized data
     * @return mixed The unserialized data
     * @throws Exception When unserialization fails
     */
    public function unserialize(string $data): mixed;

    /**
     * Check if this serializer is supported in the current environment.
     *
     * @return bool True if supported, false otherwise
     */
    public function isSupported(): bool;
}
