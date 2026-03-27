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
 * Compression serialization implementations.
 *
 * @package  Serialize
 * @category Horde
 */
class CompressionSerializer implements SerializerInterface
{
    public const MODE_BZIP = 'bzip';
    public const MODE_GZ_DEFLATE = 'gzdeflate';
    public const MODE_GZ_COMPRESS = 'gzcompress';
    public const MODE_GZ_ENCODE = 'gzencode';
    public const MODE_LZF = 'lzf';

    public function __construct(
        private readonly string $mode,
        private readonly int $level = 3,
        private readonly int $workfactor = 30,
    ) {
        if (!$this->isSupported()) {
            throw new Exception("Compression mode '{$this->mode}' is not supported");
        }
    }

    public function serialize(mixed $data): string
    {
        if (!is_string($data)) {
            throw new Exception('Compression serializer requires string input');
        }

        $result = match ($this->mode) {
            self::MODE_BZIP => bzcompress($data, $this->level, $this->workfactor),
            self::MODE_GZ_DEFLATE => gzdeflate($data, $this->level),
            self::MODE_GZ_COMPRESS => gzcompress($data, $this->level),
            self::MODE_GZ_ENCODE => gzencode($data, $this->level),
            self::MODE_LZF => lzf_compress($data),
            default => throw new Exception("Unknown compression mode: {$this->mode}"),
        };

        // bzcompress returns int on error
        if ($result === false || is_int($result)) {
            throw new Exception("Compression failed for mode: {$this->mode}");
        }

        return $result;
    }

    public function unserialize(string $data): string
    {
        $result = match ($this->mode) {
            self::MODE_BZIP => @bzdecompress($data),
            self::MODE_GZ_DEFLATE => @gzinflate($data),
            self::MODE_GZ_COMPRESS => @gzuncompress($data),
            self::MODE_GZ_ENCODE => @gzdecode($data),
            self::MODE_LZF => @lzf_decompress($data),
            default => throw new Exception("Unknown compression mode: {$this->mode}"),
        };

        if ($result === false) {
            throw new Exception("Decompression failed for mode: {$this->mode}");
        }

        return $result;
    }

    public function isSupported(): bool
    {
        return match ($this->mode) {
            self::MODE_BZIP => extension_loaded('bz2'),
            self::MODE_GZ_DEFLATE, self::MODE_GZ_COMPRESS, self::MODE_GZ_ENCODE => extension_loaded('zlib'),
            self::MODE_LZF => extension_loaded('lzf'),
            default => false,
        };
    }
}
