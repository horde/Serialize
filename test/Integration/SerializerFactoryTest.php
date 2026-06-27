<?php

declare(strict_types=1);

/**
 * Copyright 2010-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package    Serialize
 * @subpackage UnitTests
 */

namespace Horde\Serialize\Test\Integration;

use Horde\Serialize\BasicSerializer;
use Horde\Serialize\Base64Serializer;
use Horde\Serialize\ChainedSerializer;
use Horde\Serialize\CompressionSerializer;
use Horde\Serialize\JsonSerializer;
use Horde\Serialize\SerializerFactory;
use Horde\Serialize\UrlSerializer;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * @coversNothing
 */
class SerializerFactoryTest extends TestCase
{
    private SerializerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SerializerFactory();
    }

    public function testCreateJson(): void
    {
        $serializer = $this->factory->createJson();
        $this->assertInstanceOf(JsonSerializer::class, $serializer);
        $this->assertTrue($serializer->isSupported());
    }

    public function testCreateJsonWithCharset(): void
    {
        $serializer = $this->factory->createJson('ISO-8859-1');
        $this->assertInstanceOf(JsonSerializer::class, $serializer);
    }

    public function testCreateBasic(): void
    {
        $serializer = $this->factory->createBasic();
        $this->assertInstanceOf(BasicSerializer::class, $serializer);
        $this->assertTrue($serializer->isSupported());
    }

    public function testCreateBase64(): void
    {
        $serializer = $this->factory->createBase64();
        $this->assertInstanceOf(Base64Serializer::class, $serializer);
        $this->assertTrue($serializer->isSupported());
    }

    public function testCreateUrl(): void
    {
        $serializer = $this->factory->createUrl();
        $this->assertInstanceOf(UrlSerializer::class, $serializer);
        $this->assertTrue($serializer->isSupported());
    }

    public function testCreateUrlRaw(): void
    {
        $serializer = $this->factory->createUrl(true);
        $this->assertInstanceOf(UrlSerializer::class, $serializer);
    }

    public function testCreateCompressionModes(): void
    {
        $modes = [
            'createBzip' => CompressionSerializer::MODE_BZIP,
            'createGzDeflate' => CompressionSerializer::MODE_GZ_DEFLATE,
            'createGzCompress' => CompressionSerializer::MODE_GZ_COMPRESS,
            'createGzEncode' => CompressionSerializer::MODE_GZ_ENCODE,
            'createLzf' => CompressionSerializer::MODE_LZF,
        ];

        foreach ($modes as $method => $expectedMode) {
            try {
                $serializer = $this->factory->$method();
                $this->assertInstanceOf(CompressionSerializer::class, $serializer);
            } catch (Exception $e) {
                // Skip if extension not available
                $this->assertStringContainsString('not supported', $e->getMessage());
            }
        }
    }

    public function testCreateChained(): void
    {
        $basic = $this->factory->createBasic();
        $base64 = $this->factory->createBase64();

        $chained = $this->factory->createChained($basic, $base64);
        $this->assertInstanceOf(ChainedSerializer::class, $chained);
        $this->assertTrue($chained->isSupported());
    }

    public function testChainedRoundtrip(): void
    {
        $data = ['key' => 'value', 'number' => 42];

        // Create chain: Basic -> Base64
        $chained = $this->factory->createChained(
            $this->factory->createBasic(),
            $this->factory->createBase64()
        );

        $serialized = $chained->serialize($data);
        $unserialized = $chained->unserialize($serialized);

        $this->assertEquals($data, $unserialized);
    }

    public function testTripleChainedRoundtrip(): void
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('zlib extension not available');
        }

        $data = ['test' => str_repeat('data ', 100)];

        // Create chain: Basic -> GzCompress -> Base64
        $chained = $this->factory->createChained(
            $this->factory->createBasic(),
            $this->factory->createGzCompress(),
            $this->factory->createBase64()
        );

        $serialized = $chained->serialize($data);

        // Should be base64 encoded (transmission safe)
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $serialized);

        $unserialized = $chained->unserialize($serialized);
        $this->assertEquals($data, $unserialized);
    }
}
