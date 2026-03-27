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
use stdClass;
use Horde_Serialize_Exception;

#[CoversClass(Horde_Serialize::class)]
class BasicTest extends TestCase
{
    public function testBasicNull(): void
    {
        $this->assertNull(
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(null, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicBoolean(): void
    {
        $this->assertTrue(
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(true, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        $this->assertFalse(
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(false, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicIntegers(): void
    {
        $this->assertEquals(
            0,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(0, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        $this->assertEquals(
            42,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(42, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        $this->assertEquals(
            -123,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(-123, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicFloats(): void
    {
        $this->assertEquals(
            3.14,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(3.14, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        $this->assertEquals(
            -2.5,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize(-2.5, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicStrings(): void
    {
        $this->assertEquals(
            'hello world',
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize('hello world', Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // String with special characters
        $special = "Line 1\nLine 2\tTabbed\r\nWindows";
        $this->assertEquals(
            $special,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($special, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Unicode string
        $unicode = 'Héllö Wörld 世界';
        $this->assertEquals(
            $unicode,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($unicode, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Empty string
        $this->assertEquals(
            '',
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize('', Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicArrays(): void
    {
        // Indexed array
        $indexed = [1, 2, 3, 4, 5];
        $this->assertEquals(
            $indexed,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($indexed, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Associative array
        $assoc = [
            'name' => 'John',
            'age' => 30,
            'city' => 'New York',
        ];
        $this->assertEquals(
            $assoc,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($assoc, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Mixed array
        $mixed = [
            0 => 'zero',
            'key' => 'value',
            5 => 'five',
            'nested' => [1, 2, 3],
        ];
        $this->assertEquals(
            $mixed,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($mixed, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Empty array
        $this->assertEquals(
            [],
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize([], Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicNestedArrays(): void
    {
        $nested = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'data' => 'deep value',
                    ],
                ],
            ],
            'another' => ['a', 'b', 'c'],
        ];

        $this->assertEquals(
            $nested,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($nested, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicObjects(): void
    {
        // Simple object
        $obj = new stdClass();
        $obj->name = 'Test';
        $obj->value = 42;

        $this->assertEquals(
            $obj,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($obj, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Nested objects
        $nested = new stdClass();
        $nested->outer = new stdClass();
        $nested->outer->inner = new stdClass();
        $nested->outer->inner->value = 'nested';

        $this->assertEquals(
            $nested,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($nested, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicMixedStructures(): void
    {
        // Object containing arrays
        $obj = new stdClass();
        $obj->data = [1, 2, 3];
        $obj->assoc = ['key' => 'value'];

        $this->assertEquals(
            $obj,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($obj, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );

        // Array containing objects
        $inner = new stdClass();
        $inner->id = 1;
        $arr = [
            'item' => $inner,
            'name' => 'test',
        ];

        $this->assertEquals(
            $arr,
            Horde_Serialize::unserialize(
                Horde_Serialize::serialize($arr, Horde_Serialize::BASIC),
                Horde_Serialize::BASIC
            )
        );
    }

    public function testBasicFalseValue(): void
    {
        // Special test for false value - unserialize returns false on error
        // AND when the actual value is false
        $serialized = Horde_Serialize::serialize(false, Horde_Serialize::BASIC);
        $this->assertFalse(
            Horde_Serialize::unserialize($serialized, Horde_Serialize::BASIC)
        );
        $this->assertEquals('b:0;', $serialized);
    }

    public function testBasicUnserializeInvalidData(): void
    {
        // Invalid serialized data should throw exception
        $this->expectException(Horde_Serialize_Exception::class);
        Horde_Serialize::unserialize('invalid serialized data', Horde_Serialize::BASIC);
    }
}
