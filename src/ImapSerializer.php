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
use Horde_Imap_Client_Utf7imap;
use Horde_Mime;

/**
 * IMAP-related serialization implementations.
 *
 * Note: Uses legacy Horde_Imap_Client and Horde_Mime classes as PSR-4
 * equivalents are not yet available in those components.
 *
 * @package  Serialize
 * @category Horde
 */
class ImapSerializer implements SerializerInterface
{
    public const MODE_IMAP8 = 'imap8';
    public const MODE_IMAPUTF7 = 'imaputf7';
    public const MODE_IMAPUTF8 = 'imaputf8';

    public function __construct(
        private readonly string $mode,
    ) {
        if (!$this->isSupported()) {
            throw new Exception("IMAP mode '{$this->mode}' is not supported");
        }
    }

    public function serialize(mixed $data): string
    {
        if (!is_string($data)) {
            throw new Exception('IMAP serializer requires string input');
        }

        return match ($this->mode) {
            self::MODE_IMAP8 => quoted_printable_encode($data),
            self::MODE_IMAPUTF7 => Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap(
                HordeString::convertCharset($data, 'ISO-8859-1', 'UTF-8')
            ),
            self::MODE_IMAPUTF8 => Horde_Mime::decode($data),
            default => throw new Exception("Unknown IMAP mode: {$this->mode}"),
        };
    }

    public function unserialize(string $data): string
    {
        return match ($this->mode) {
            self::MODE_IMAP8 => quoted_printable_decode($data),
            self::MODE_IMAPUTF7 => HordeString::convertCharset(
                Horde_Imap_Client_Utf7imap::Utf7ImapToUtf8($data),
                'UTF-8',
                'ISO-8859-1'
            ),
            self::MODE_IMAPUTF8 => Horde_Mime::encode($data),
            default => throw new Exception("Unknown IMAP mode: {$this->mode}"),
        };
    }

    public function isSupported(): bool
    {
        return match ($this->mode) {
            self::MODE_IMAP8 => true,
            self::MODE_IMAPUTF7 => class_exists('Horde_Imap_Client'),
            self::MODE_IMAPUTF8 => class_exists('Horde_Mime'),
            default => false,
        };
    }
}
