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
use Horde_Serialize_Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Horde_Serialize::class)]
class SerializeTest extends TestCase
{
    // hasCapability() tests
    public function testHasCapabilityAlwaysSupported(): void
    {
        // Modes that should always be supported
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::NONE));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::BASIC));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::BASE64));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::IMAP8));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::RAW));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::URL));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::UTF7));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::UTF7_BASIC));
        $this->assertTrue(Horde_Serialize::hasCapability(Horde_Serialize::JSON));
    }

    public function testHasCapabilityExtensionDependent(): void
    {
        // BZIP requires bz2 extension
        $this->assertEquals(
            extension_loaded('bz2'),
            Horde_Serialize::hasCapability(Horde_Serialize::BZIP)
        );

        // WDDX requires wddx extension (deprecated/removed in PHP 8+)
        $this->assertEquals(
            extension_loaded('wddx'),
            Horde_Serialize::hasCapability(Horde_Serialize::WDDX)
        );

        // GZ modes require zlib
        $hasZlib = extension_loaded('zlib');
        $this->assertEquals($hasZlib, Horde_Serialize::hasCapability(Horde_Serialize::GZ_DEFLATE));
        $this->assertEquals($hasZlib, Horde_Serialize::hasCapability(Horde_Serialize::GZ_COMPRESS));
        $this->assertEquals($hasZlib, Horde_Serialize::hasCapability(Horde_Serialize::GZ_ENCODE));

        // LZF requires lzf extension
        $this->assertEquals(
            extension_loaded('lzf'),
            Horde_Serialize::hasCapability(Horde_Serialize::LZF)
        );
    }

    public function testHasCapabilityClassDependent(): void
    {
        // IMAPUTF7 requires Horde_Imap_Client
        $this->assertEquals(
            class_exists('Horde_Imap_Client'),
            Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF7)
        );

        // IMAPUTF8 requires Horde_Mime
        $this->assertEquals(
            class_exists('Horde_Mime'),
            Horde_Serialize::hasCapability(Horde_Serialize::IMAPUTF8)
        );
    }

    public function testHasCapabilityUnknownMode(): void
    {
        // UNKNOWN mode should return false
        $this->assertFalse(Horde_Serialize::hasCapability(Horde_Serialize::UNKNOWN));

        // Invalid mode number
        $this->assertFalse(Horde_Serialize::hasCapability(999));
    }

    // Unsupported mode tests
    public function testSerializeUnsupportedMode(): void
    {
        $this->expectException(Horde_Serialize_Exception::class);
        $this->expectExceptionMessage('Unsupported serialization type');

        // Try to use WDDX if not available (or another unsupported mode)
        if (!Horde_Serialize::hasCapability(Horde_Serialize::WDDX)) {
            Horde_Serialize::serialize('test', Horde_Serialize::WDDX);
        } else {
            // Use an invalid mode number
            Horde_Serialize::serialize('test', 999);
        }
    }

    public function testUnserializeUnsupportedMode(): void
    {
        $this->expectException(Horde_Serialize_Exception::class);
        $this->expectExceptionMessage('Unsupported unserialization type');

        // Try to use WDDX if not available (or another unsupported mode)
        if (!Horde_Serialize::hasCapability(Horde_Serialize::WDDX)) {
            Horde_Serialize::unserialize('test', Horde_Serialize::WDDX);
        } else {
            // Use an invalid mode number
            Horde_Serialize::unserialize('test', 999);
        }
    }

    // Chained mode tests
    public function testChainedModesMultipleOperations(): void
    {
        $data = ['test' => 'data', 'number' => 42];

        // Chain: BASIC -> BASE64 -> URL
        $encoded = Horde_Serialize::serialize(
            $data,
            [Horde_Serialize::BASIC, Horde_Serialize::BASE64, Horde_Serialize::URL]
        );

        // Reverse: URL -> BASE64 -> BASIC
        $decoded = Horde_Serialize::unserialize(
            $encoded,
            [Horde_Serialize::URL, Horde_Serialize::BASE64, Horde_Serialize::BASIC]
        );

        $this->assertEquals($data, $decoded);
    }

    public function testChainedModesSingleMode(): void
    {
        // Test that single mode in array works the same as single mode
        $data = 'test';

        $single = Horde_Serialize::serialize($data, Horde_Serialize::BASE64);
        $array = Horde_Serialize::serialize($data, [Horde_Serialize::BASE64]);

        $this->assertEquals($single, $array);
    }

    public function testChainedModesEmptyArray(): void
    {
        // Empty mode array should throw exception or behave predictably
        $data = 'test';

        try {
            $encoded = Horde_Serialize::serialize($data, []);
            // If it doesn't throw, it should return the data unchanged
            $this->assertEquals($data, $encoded);
        } catch (Horde_Serialize_Exception $e) {
            // Exception is acceptable behavior
            $this->assertInstanceOf(Horde_Serialize_Exception::class, $e);
        }
    }

    // Error handling tests
    public function testSerializeFailureThrowsException(): void
    {
        if (Horde_Serialize::hasCapability(Horde_Serialize::BZIP)) {
            $this->expectException(Horde_Serialize_Exception::class);
            $this->expectExceptionMessage('Serialization failed');

            // BZip can fail with invalid compression parameters
            // This is tricky to trigger, so we might need to revisit this
            $data = str_repeat('x', 1000000); // Large data
            Horde_Serialize::serialize($data, Horde_Serialize::BZIP, ['level' => 999]);
        } else {
            $this->markTestSkipped('Cannot test bzip failure without bz2 extension');
        }
    }

    public function testUnserializeFailureThrowsException(): void
    {
        $this->expectException(Horde_Serialize_Exception::class);
        $this->expectExceptionMessage('Unserialization failed');

        // Suppress expected warnings from invalid compressed data
        @Horde_Serialize::unserialize('not compressed data', Horde_Serialize::GZ_DEFLATE);
    }

    // Default parameter tests
    public function testSerializeDefaultMode(): void
    {
        // Default mode is BASIC (as an array)
        $data = ['test' => 'value'];
        $serialized = Horde_Serialize::serialize($data);

        // Should be PHP serialized format
        $this->assertStringStartsWith('a:', $serialized);
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($serialized)
        );
    }

    // WDDX tests (if available)
    public function testWddxSerialization(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::WDDX)) {
            $this->markTestSkipped('wddx extension not available (removed in PHP 8.0)');
        }

        $data = ['key' => 'value', 'number' => 42];
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::WDDX);

        // WDDX produces XML
        $this->assertStringContainsString('<wddxPacket', $encoded);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::WDDX)
        );
    }
}
