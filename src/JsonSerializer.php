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

use Horde\Util\HordeString;

/**
 * JSON serialization implementation.
 *
 * @package  Serialize
 * @category Horde
 */
class JsonSerializer implements SerializerInterface
{
    public function __construct(
        private readonly ?string $sourceCharset = null,
    ) {
    }

    public function serialize(mixed $data): string
    {
        $tmp = json_encode($data);

        // Handle UTF-8 encoding errors
        if (json_last_error() === JSON_ERROR_UTF8) {
            $charset = $this->sourceCharset ?? 'UTF-8';
            $data = json_encode(HordeString::convertCharset($data, $charset, 'UTF-8', true));
            if ($data === false) {
                throw new Exception('JSON serialization failed: ' . json_last_error_msg());
            }
            return $data;
        }

        if ($tmp === false) {
            throw new Exception('JSON serialization failed: ' . json_last_error_msg());
        }

        return $tmp;
    }

    public function unserialize(string $data): mixed
    {
        $out = json_decode($data);

        if (!is_null($out) || strcasecmp($data, 'null') === 0) {
            return $out;
        }

        throw new Exception('JSON unserialization failed: ' . json_last_error_msg());
    }

    public function isSupported(): bool
    {
        return true; // JSON support is built into PHP
    }
}
