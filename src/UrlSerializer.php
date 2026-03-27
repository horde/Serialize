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
 * URL encoding implementation.
 *
 * @package  Serialize
 * @category Horde
 */
class UrlSerializer implements SerializerInterface
{
    public function __construct(
        private readonly bool $raw = false,
    ) {
    }

    public function serialize(mixed $data): string
    {
        if (!is_string($data)) {
            throw new Exception('URL serializer requires string input');
        }

        return $this->raw ? rawurlencode($data) : urlencode($data);
    }

    public function unserialize(string $data): string
    {
        return $this->raw ? rawurldecode($data) : urldecode($data);
    }

    public function isSupported(): bool
    {
        return true;
    }
}
