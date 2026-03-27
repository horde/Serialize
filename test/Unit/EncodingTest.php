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
class EncodingTest extends TestCase
{
    // NONE mode tests
    public function testNonePassthrough(): void
    {
        $data = 'test data';
        $this->assertEquals(
            $data,
            Horde_Serialize::serialize($data, Horde_Serialize::NONE)
        );

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($data, Horde_Serialize::NONE)
        );
    }

    // BASE64 mode tests
    public function testBase64SimpleString(): void
    {
        $data = 'Hello World';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::BASE64);

        $this->assertEquals('SGVsbG8gV29ybGQ=', $encoded);
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::BASE64)
        );
    }

    public function testBase64BinaryData(): void
    {
        // Binary data with null bytes and special characters
        $binary = "\x00\x01\x02\xFF\xFE\xFD";
        $encoded = Horde_Serialize::serialize($binary, Horde_Serialize::BASE64);

        $this->assertEquals(
            $binary,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::BASE64)
        );
    }

    public function testBase64EmptyString(): void
    {
        $this->assertEquals(
            '',
            Horde_Serialize::serialize('', Horde_Serialize::BASE64)
        );

        $this->assertEquals(
            '',
            Horde_Serialize::unserialize('', Horde_Serialize::BASE64)
        );
    }

    public function testBase64SpecialCharacters(): void
    {
        $data = "Line1\nLine2\r\nTab\tQuote\"Backslash\\";
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::BASE64);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::BASE64)
        );
    }

    // URL encoding tests
    public function testUrlEncoding(): void
    {
        $data = 'hello world';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::URL);

        $this->assertEquals('hello+world', $encoded);
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::URL)
        );
    }

    public function testUrlSpecialCharacters(): void
    {
        $data = 'special: @#$%^&*()+={}[]|\\:";\'<>,.?/';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::URL);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::URL)
        );
    }

    public function testUrlUnicode(): void
    {
        $data = 'Héllö Wörld';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::URL);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::URL)
        );
    }

    public function testUrlEmptyString(): void
    {
        $this->assertEquals(
            '',
            Horde_Serialize::serialize('', Horde_Serialize::URL)
        );

        $this->assertEquals(
            '',
            Horde_Serialize::unserialize('', Horde_Serialize::URL)
        );
    }

    // RAW URL encoding tests
    public function testRawUrlEncoding(): void
    {
        $data = 'hello world';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::RAW);

        $this->assertEquals('hello%20world', $encoded);
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::RAW)
        );
    }

    public function testRawSpecialCharacters(): void
    {
        $data = 'special: ~!@#$%^&*()';
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::RAW);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::RAW)
        );
    }

    public function testRawVsUrlDifference(): void
    {
        // RAW uses %20 for space, URL uses +
        $data = 'hello world';
        $raw = Horde_Serialize::serialize($data, Horde_Serialize::RAW);
        $url = Horde_Serialize::serialize($data, Horde_Serialize::URL);

        $this->assertEquals('hello%20world', $raw);
        $this->assertEquals('hello+world', $url);
        $this->assertNotEquals($raw, $url);

        // But both decode correctly
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($raw, Horde_Serialize::RAW)
        );
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($url, Horde_Serialize::URL)
        );
    }

    // Chained encoding tests
    public function testChainedBasicAndBase64(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $encoded = Horde_Serialize::serialize(
            $data,
            [Horde_Serialize::BASIC, Horde_Serialize::BASE64]
        );

        // Should be base64-encoded serialized PHP array
        $this->assertIsString($encoded);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encoded);

        $decoded = Horde_Serialize::unserialize(
            $encoded,
            [Horde_Serialize::BASE64, Horde_Serialize::BASIC]
        );

        $this->assertEquals($data, $decoded);
    }

    public function testChainedBasicAndUrl(): void
    {
        $data = ['test' => 'hello world'];
        $encoded = Horde_Serialize::serialize(
            $data,
            [Horde_Serialize::BASIC, Horde_Serialize::URL]
        );

        $decoded = Horde_Serialize::unserialize(
            $encoded,
            [Horde_Serialize::URL, Horde_Serialize::BASIC]
        );

        $this->assertEquals($data, $decoded);
    }
}
