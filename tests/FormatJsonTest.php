<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Suitcase\Format\Json;

class FormatJsonTest extends TestCase
{
    /**
     * Test that data is properly encoded to JSON.
     */
    public function testEncode(): void
    {
        $json = file_get_contents(__DIR__ . '/data/test.json');
        $array = [
          'foo' => [
            'bar' => 'baz',
          ],
        ];

        $encoded = Json::encode($array);
        $this->assertEquals($json, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     */
    public function testDecode(): void
    {
        $json = file_get_contents(__DIR__ . '/data/test.json');
        $array = [
          'foo' => [
            'bar' => 'baz',
          ],
        ];

        $decoded = Json::decode($json);
        $this->assertEquals($array, $decoded);
    }

    /**
     * Test that an invalid array throws an exception.
     */
    public function testInvalidEncode(): void
    {
        $this->expectException(\Exception::class);

        $array = ['foo' => chr(255)];
        $encoded = Json::encode($array);
    }

    /**
     * Test that invalid JSON throws an exception.
     */
    public function testInvalidDecode(): void
    {
        $this->expectException(\Exception::class);

        $json = file_get_contents(__DIR__ . '/data/invalid.json');
        $decoded = Json::decode($json);
    }
}
