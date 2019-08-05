<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Suitcase\Format\Json;

class FormatJsonTest extends TestCase
{
    /**
     * Provides JSON data for store tests.
     *
     * @return array
     *   An array of data.
     */
    public function jsonDataProvider(): array
    {
        return [
            [
                [
                    'foo' => [
                        'bar' => 'baz',
                    ],
                ],
            ],
        ];
    }

    /**
     * Test that data is properly encoded to JSON.
     *
     * @dataProvider jsonDataProvider
     */
    public function testEncode($array): void
    {
        $json = file_get_contents(__DIR__ . '/data/data.json');
        $encoded = Json::encode($array);
        $this->assertEquals($json, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     *
     * @dataProvider jsonDataProvider
     */
    public function testDecode($array): void
    {
        $json = file_get_contents(__DIR__ . '/data/data.json');
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
