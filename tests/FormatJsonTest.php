<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Suitcase\Format\Json;

class FormatJsonTest extends TestCase
{

    public function testEncode()
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

    public function testDecode()
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

    public function testInvalidEncode()
    {
        $this->expectException(\Exception::class);

        $array = ['foo' => chr(255)];
        $encoded = Json::encode($array);
    }

    public function testInvalidDecode()
    {
        $this->expectException(\Exception::class);

        $json = file_get_contents(__DIR__ . '/data/invalid.json');
        $decoded = Json::decode($json);
    }
}
