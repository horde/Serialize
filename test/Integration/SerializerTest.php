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
use Horde\Serialize\Exception;
use Horde\Serialize\JsonSerializer;
use Horde\Serialize\NoneSerializer;
use Horde\Serialize\SerializerFactory;
use Horde\Serialize\UrlSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversNothing
 */
class SerializerTest extends TestCase
{
    // JsonSerializer tests
    public function testJsonSerializerBasic(): void
    {
        $serializer = new JsonSerializer();

        $data = ['key' => 'value', 'number' => 42];
        $json = $serializer->serialize($data);

        $this->assertJson($json);

        // JSON decodes arrays with string keys as objects
        $result = $serializer->unserialize($json);
        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
        $this->assertEquals(42, $result->number);
    }

    public function testJsonSerializerObjects(): void
    {
        $serializer = new JsonSerializer();

        $obj = new stdClass();
        $obj->name = 'test';
        $obj->value = 123;

        $json = $serializer->serialize($obj);
        $result = $serializer->unserialize($json);

        $this->assertEquals($obj, $result);
    }

    public function testJsonSerializerNull(): void
    {
        $serializer = new JsonSerializer();
        $this->assertNull($serializer->unserialize('null'));
    }

    // BasicSerializer tests
    public function testBasicSerializerArrays(): void
    {
        $serializer = new BasicSerializer();

        $data = ['a' => 1, 'b' => [2, 3, 4]];
        $serialized = $serializer->serialize($data);

        $this->assertStringStartsWith('a:', $serialized);
        $this->assertEquals($data, $serializer->unserialize($serialized));
    }

    public function testBasicSerializerObjects(): void
    {
        $serializer = new BasicSerializer();

        $obj = new stdClass();
        $obj->prop = 'value';

        $serialized = $serializer->serialize($obj);
        $unserialized = $serializer->unserialize($serialized);

        $this->assertEquals($obj, $unserialized);
    }

    public function testBasicSerializerFalse(): void
    {
        $serializer = new BasicSerializer();

        $serialized = $serializer->serialize(false);
        $this->assertFalse($serializer->unserialize($serialized));
    }

    public function testBasicSerializerInvalidDataThrows(): void
    {
        $serializer = new BasicSerializer();

        $this->expectException(Exception::class);
        $serializer->unserialize('invalid data');
    }

    // Base64Serializer tests
    public function testBase64SerializerRoundtrip(): void
    {
        $serializer = new Base64Serializer();

        $data = 'Hello World!';
        $encoded = $serializer->serialize($data);

        $this->assertEquals('SGVsbG8gV29ybGQh', $encoded);
        $this->assertEquals($data, $serializer->unserialize($encoded));
    }

    public function testBase64SerializerBinary(): void
    {
        $serializer = new Base64Serializer();

        $binary = "\x00\x01\x02\xFF";
        $encoded = $serializer->serialize($binary);

        $this->assertEquals($binary, $serializer->unserialize($encoded));
    }

    public function testBase64SerializerRequiresString(): void
    {
        $serializer = new Base64Serializer();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('requires string input');
        $serializer->serialize(['not', 'a', 'string']);
    }

    // UrlSerializer tests
    public function testUrlSerializerStandard(): void
    {
        $serializer = new UrlSerializer();

        $data = 'hello world';
        $encoded = $serializer->serialize($data);

        $this->assertEquals('hello+world', $encoded);
        $this->assertEquals($data, $serializer->unserialize($encoded));
    }

    public function testUrlSerializerRaw(): void
    {
        $serializer = new UrlSerializer(true);

        $data = 'hello world';
        $encoded = $serializer->serialize($data);

        $this->assertEquals('hello%20world', $encoded);
        $this->assertEquals($data, $serializer->unserialize($encoded));
    }

    public function testUrlSerializerSpecialChars(): void
    {
        $serializer = new UrlSerializer();

        $data = 'test@example.com?foo=bar&baz=qux';
        $encoded = $serializer->serialize($data);

        $this->assertEquals($data, $serializer->unserialize($encoded));
    }

    // NoneSerializer tests
    public function testNoneSerializerPassthrough(): void
    {
        $serializer = new NoneSerializer();

        $data = 'passthrough data';
        $this->assertEquals($data, $serializer->serialize($data));
        $this->assertEquals($data, $serializer->unserialize($data));
    }

    // ChainedSerializer tests
    public function testChainedSerializerBasicAndBase64(): void
    {
        $chained = new ChainedSerializer([
            new BasicSerializer(),
            new Base64Serializer(),
        ]);

        $data = ['array' => [1, 2, 3]];
        $encoded = $chained->serialize($data);

        // Should be base64 encoded
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $encoded);

        $decoded = $chained->unserialize($encoded);
        $this->assertEquals($data, $decoded);
    }

    public function testChainedSerializerEmptyArrayThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('at least one serializer');
        new ChainedSerializer([]);
    }

    public function testChainedSerializerInvalidTypeThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('must implement SerializerInterface');
        new ChainedSerializer(['not a serializer']);
    }

    // Real-world usage patterns
    public function testCompressAndEncodeForTransport(): void
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('zlib extension not available');
        }

        $factory = new SerializerFactory();

        // Common pattern: serialize, compress, base64 for safe transport
        $chained = $factory->createChained(
            $factory->createBasic(),
            $factory->createGzCompress(9), // Max compression
            $factory->createBase64()
        );

        $data = [
            'user' => 'john@example.com',
            'data' => str_repeat('some repetitive data ', 100),
        ];

        $encoded = $chained->serialize($data);

        // Should be much smaller than original due to compression
        $uncompressed = serialize($data);
        $this->assertLessThan(strlen($uncompressed), strlen(base64_decode($encoded)));

        // Should decode correctly
        $decoded = $chained->unserialize($encoded);
        $this->assertEquals($data, $decoded);
    }

    public function testSerializeForUrlTransmission(): void
    {
        $factory = new SerializerFactory();

        // Pattern: serialize, then URL encode for GET parameter
        $chained = $factory->createChained(
            $factory->createBasic(),
            $factory->createUrl()
        );

        $data = ['param' => 'value with spaces'];
        $encoded = $chained->serialize($data);

        // Should be URL-safe
        $this->assertStringNotContainsString(' ', $encoded);

        $decoded = $chained->unserialize($encoded);
        $this->assertEquals($data, $decoded);
    }
}
