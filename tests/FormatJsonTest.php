<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Ivory\Serializer\SerializerInterface;
use Ivory\Serializer\Format;
use Suitcase\Format\Json;
use Suitcase\Exception\FormatException;

class FormatJsonTest extends TestCase
{
    /**
     * A prophesized mock serializer object.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $serializer;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->serializer = self::prophesize(SerializerInterface::class);
    }

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
     * Test that the proper file extension is returned.
     */
    public function testFileExtension(): void
    {
        $formatter = new Json($this->serializer->reveal());
        $this->assertEquals('.json', $formatter->getExtension());
    }

    /**
     * Test that data is properly encoded to JSON.
     *
     * @dataProvider jsonDataProvider
     */
    public function testEncode($array): void
    {
        $json = '{"foo":{"bar":"baz"}}';
        $this->serializer->serialize($array, Format::JSON)->willReturn($json);
        $formatter = new Json($this->serializer->reveal());

        $encoded = $formatter->encode($array);
        $this->assertEquals($json, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     *
     * @dataProvider jsonDataProvider
     */
    public function testDecode($array): void
    {
        $json = '{"foo":{"bar":"baz"}}';
        $this->serializer->deserialize($json, 'array', Format::JSON)->willReturn($array);
        $formatter = new Json($this->serializer->reveal());

        $decoded = $formatter->decode($json);
        $this->assertEquals($array, $decoded);
    }

    /**
     * Test that an invalid array throws an exception.
     */
    public function testInvalidEncode(): void
    {
        $this->expectException(FormatException::class);

        $array = ['foo' => chr(255)];
        $this->serializer->serialize($array, Format::JSON)->willThrow(\InvalidArgumentException::class);
        $formatter = new Json($this->serializer->reveal());

        $formatter->encode($array);
    }

    /**
     * Test that invalid JSON throws an exception.
     */
    public function testInvalidDecode(): void
    {
        $this->expectException(FormatException::class);

        $json = '{"foo": {"bar":}}';
        $this->serializer->deserialize($json, 'array', Format::JSON)->willThrow(\InvalidArgumentException::class);
        $formatter = new Json($this->serializer->reveal());

        $formatter->decode($json);
    }
}
