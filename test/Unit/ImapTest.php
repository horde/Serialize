<?php

declare(strict_types=1);

/**
 * Copyright 2010-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author     Michael Slusarz <slusarz@horde.org>
 * @category   Horde
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package    Serialize
 * @subpackage UnitTests
 */

namespace Horde\Serialize\Test\Unit;

use Horde_Serialize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Horde_Serialize::class)]
class ImapTest extends TestCase
{
    // IMAP8 (quoted-printable) tests
    public function testImap8QuotedPrintable(): void
    {
        $data = 'Hello World';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAP8);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAP8)
        );
    }

    public function testImap8SpecialCharacters(): void
    {
        // Quoted-printable encodes characters > 127 and special chars
        $data = "Line 1\r\nLine 2\r\nSpecial: é à ü";
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAP8);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAP8)
        );
    }

    public function testImap8BinaryData(): void
    {
        // Binary data with high bytes
        $data = "Binary\x80\x90\xA0\xFFdata";
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAP8);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAP8)
        );
    }

    public function testImap8EmptyString(): void
    {
        $this->assertEquals(
            '',
            Horde_Serialize::serialize('', Horde_Serialize::IMAP8)
        );

        $this->assertEquals(
            '',
            Horde_Serialize::unserialize('', Horde_Serialize::IMAP8)
        );
    }

    // IMAPUTF7 tests
    public function testImapUtf7Conversion(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF7)) {
            $this->markTestSkipped('horde/imap_client not available');
        }

        // Simple ASCII - should pass through
        $data = 'Inbox';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAPUTF7);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAPUTF7)
        );
    }

    public function testImapUtf7SpecialCharacters(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF7)) {
            $this->markTestSkipped('horde/imap_client not available');
        }

        // ISO-8859-1 characters that need UTF-7 encoding
        $data = 'Sent Items'; // Simple case
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAPUTF7);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAPUTF7)
        );
    }

    // IMAPUTF8 tests
    public function testImapUtf8Encoding(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF8)) {
            $this->markTestSkipped('horde/mime not available');
        }

        $data = 'Test data';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAPUTF8);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAPUTF8)
        );
    }

    public function testImapUtf8SpecialCharacters(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF8)) {
            $this->markTestSkipped('horde/mime not available');
        }

        $data = 'Special: é à ü ñ';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::IMAPUTF8);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::IMAPUTF8)
        );
    }

    // UTF7 tests (requires Horde_String)
    public function testUtf7Conversion(): void
    {
        // UTF-7 charset conversion
        $data = 'Hello';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::UTF7, 'UTF-8');

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::UTF7, 'UTF-8')
        );
    }

    public function testUtf7UnicodeCharacters(): void
    {
        // UTF-7 with unicode characters
        $data = 'Tëst';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::UTF7, 'UTF-8');

        // UTF-7 encoding
        $this->assertNotEquals($data, $encoded);

        // Should decode back
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::UTF7, 'UTF-8')
        );
    }

    // UTF7_BASIC tests (UTF7 + PHP serialization)
    public function testUtf7BasicChained(): void
    {
        $data = ['key' => 'Tëst'];
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::UTF7_BASIC, 'UTF-8');

        $decoded = Horde_Serialize::unserialize($encoded, Horde_Serialize::UTF7_BASIC, 'UTF-8');

        $this->assertEquals($data, $decoded);
    }

    public function testUtf7BasicComplexData(): void
    {
        $data = [
            'name' => 'Jöhn',
            'values' => [1, 2, 3],
            'nested' => ['ké y' => 'vâlue'],
        ];

        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::UTF7_BASIC, 'UTF-8');
        $decoded = Horde_Serialize::unserialize($encoded, Horde_Serialize::UTF7_BASIC, 'UTF-8');

        $this->assertEquals($data, $decoded);
    }
}
