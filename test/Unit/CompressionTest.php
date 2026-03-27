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
class CompressionTest extends TestCase
{
    // BZIP compression tests
    public function testBzipCompression(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::BZIP)) {
            $this->markTestSkipped('bz2 extension not available');
        }

        $data = 'This is some data to compress. ' . str_repeat('Repetitive text. ', 100);
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::BZIP);

        // Compressed data should be smaller
        $this->assertLessThan(strlen($data), strlen($compressed));

        // Should decompress correctly
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::BZIP)
        );
    }

    public function testBzipCompressionLevels(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::BZIP)) {
            $this->markTestSkipped('bz2 extension not available');
        }

        $data = str_repeat('Test data for compression level testing. ', 50);

        // Test different compression levels (1-9)
        $level1 = Horde_Serialize::serialize($data, Horde_Serialize::BZIP, ['level' => 1]);
        $level9 = Horde_Serialize::serialize($data, Horde_Serialize::BZIP, ['level' => 9]);

        // Both should decompress correctly
        $this->assertEquals($data, Horde_Serialize::unserialize($level1, Horde_Serialize::BZIP));
        $this->assertEquals($data, Horde_Serialize::unserialize($level9, Horde_Serialize::BZIP));

        // Higher compression should produce smaller output (usually)
        $this->assertLessThanOrEqual(strlen($level1), strlen($level9));
    }

    public function testBzipShortString(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::BZIP)) {
            $this->markTestSkipped('bz2 extension not available');
        }

        $data = 'short';
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::BZIP);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::BZIP)
        );
    }

    // GZ_DEFLATE tests
    public function testGzDeflateCompression(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_DEFLATE)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = 'Test data for deflate. ' . str_repeat('Repeated content. ', 100);
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::GZ_DEFLATE);

        $this->assertLessThan(strlen($data), strlen($compressed));
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::GZ_DEFLATE)
        );
    }

    public function testGzDeflateCompressionLevels(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_DEFLATE)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = str_repeat('Compression level test data. ', 50);

        $level0 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_DEFLATE, ['level' => 0]);
        $level9 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_DEFLATE, ['level' => 9]);

        $this->assertEquals($data, Horde_Serialize::unserialize($level0, Horde_Serialize::GZ_DEFLATE));
        $this->assertEquals($data, Horde_Serialize::unserialize($level9, Horde_Serialize::GZ_DEFLATE));
    }

    // GZ_COMPRESS tests
    public function testGzCompressCompression(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_COMPRESS)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = 'Test data for compress. ' . str_repeat('More data. ', 100);
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::GZ_COMPRESS);

        $this->assertLessThan(strlen($data), strlen($compressed));
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::GZ_COMPRESS)
        );
    }

    public function testGzCompressCompressionLevels(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_COMPRESS)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = str_repeat('Test compress levels. ', 50);

        $level1 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_COMPRESS, ['level' => 1]);
        $level9 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_COMPRESS, ['level' => 9]);

        $this->assertEquals($data, Horde_Serialize::unserialize($level1, Horde_Serialize::GZ_COMPRESS));
        $this->assertEquals($data, Horde_Serialize::unserialize($level9, Horde_Serialize::GZ_COMPRESS));
    }

    // GZ_ENCODE tests (gzencode - RFC 1952 format)
    public function testGzEncodeCompression(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_ENCODE)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = 'Test data for gzencode. ' . str_repeat('Repeated. ', 100);
        $encoded = Horde_Serialize::serialize($data, Horde_Serialize::GZ_ENCODE);

        $this->assertLessThan(strlen($data), strlen($encoded));
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($encoded, Horde_Serialize::GZ_ENCODE)
        );
    }

    public function testGzEncodeCompressionLevels(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_ENCODE)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = str_repeat('Encode level test. ', 50);

        $level1 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_ENCODE, ['level' => 1]);
        $level9 = Horde_Serialize::serialize($data, Horde_Serialize::GZ_ENCODE, ['level' => 9]);

        $this->assertEquals($data, Horde_Serialize::unserialize($level1, Horde_Serialize::GZ_ENCODE));
        $this->assertEquals($data, Horde_Serialize::unserialize($level9, Horde_Serialize::GZ_ENCODE));
    }

    // LZF compression tests
    public function testLzfCompression(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::LZF)) {
            $this->markTestSkipped('lzf extension not available');
        }

        $data = 'LZF compression test. ' . str_repeat('Data to compress. ', 100);
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::LZF);

        $this->assertLessThan(strlen($data), strlen($compressed));
        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::LZF)
        );
    }

    public function testLzfShortString(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::LZF)) {
            $this->markTestSkipped('lzf extension not available');
        }

        $data = 'short';
        $compressed = Horde_Serialize::serialize($data, Horde_Serialize::LZF);

        $this->assertEquals(
            $data,
            Horde_Serialize::unserialize($compressed, Horde_Serialize::LZF)
        );
    }

    // Chained compression and encoding tests
    public function testChainedBasicAndGzCompress(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_COMPRESS)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = ['array' => [1, 2, 3], 'string' => 'test'];

        // Serialize then compress
        $encoded = Horde_Serialize::serialize(
            $data,
            [Horde_Serialize::BASIC, Horde_Serialize::GZ_COMPRESS]
        );

        // Decompress then unserialize
        $decoded = Horde_Serialize::unserialize(
            $encoded,
            [Horde_Serialize::GZ_COMPRESS, Horde_Serialize::BASIC]
        );

        $this->assertEquals($data, $decoded);
    }

    public function testChainedBasicGzAndBase64(): void
    {
        if (!Horde_Serialize::hasCapability(Horde_Serialize::GZ_DEFLATE)) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = ['test' => str_repeat('data ', 50)];

        // Serialize, compress, then base64
        $encoded = Horde_Serialize::serialize(
            $data,
            [Horde_Serialize::BASIC, Horde_Serialize::GZ_DEFLATE, Horde_Serialize::BASE64]
        );

        // Should be base64 (safe for transmission)
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encoded);

        // Reverse: base64 decode, decompress, unserialize
        $decoded = Horde_Serialize::unserialize(
            $encoded,
            [Horde_Serialize::BASE64, Horde_Serialize::GZ_DEFLATE, Horde_Serialize::BASIC]
        );

        $this->assertEquals($data, $decoded);
    }
}
