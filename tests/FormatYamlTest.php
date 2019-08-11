<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Ivory\Serializer\SerializerInterface;
use Ivory\Serializer\Format;
use Suitcase\Format\Yaml;
use Suitcase\Exception\FormatException;

class FormatYamlTest extends TestCase
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
    public function yamlDataProvider(): array
    {
        return [
            [
                [
                    'foo' => [
                        'bar' => 'baz',
                    ],
                ],
                "foo:\n\tbar: baz",
            ],
        ];
    }

    /**
     * Test that the proper file extension is returned.
     */
    public function testFileExtension(): void
    {
        $formatter = new Yaml($this->serializer->reveal());
        $this->assertEquals('.yml', $formatter->getExtension());
    }

    /**
     * Test that data is properly encoded to JSON.
     *
     * @dataProvider yamlDataProvider
     */
    public function testEncode($array, $yaml): void
    {
        $this->serializer->serialize($array, Format::YAML)->willReturn($yaml);
        $formatter = new Yaml($this->serializer->reveal());

        $encoded = $formatter->encode($array);
        $this->assertEquals($yaml, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     *
     * @dataProvider yamlDataProvider
     */
    public function testDecode($array, $yaml): void
    {
        $this->serializer->deserialize($yaml, 'array', Format::YAML)->willReturn($array);
        $formatter = new Yaml($this->serializer->reveal());

        $decoded = $formatter->decode($yaml);
        $this->assertEquals($array, $decoded);
    }

    /**
     * Test that an invalid array throws an exception.
     */
    public function testInvalidEncode(): void
    {
        $this->expectException(FormatException::class);

        $array = ['foo' => chr(255)];
        $this->serializer->serialize($array, Format::YAML)->willThrow(\InvalidArgumentException::class);
        $formatter = new Yaml($this->serializer->reveal());

        $formatter->encode($array);
    }

    /**
     * Test that invalid JSON throws an exception.
     */
    public function testInvalidDecode(): void
    {
        $this->expectException(FormatException::class);

        $yaml = ":foo:\n\tbar: baz";
        $this->serializer->deserialize($yaml, 'array', Format::YAML)->willThrow(\InvalidArgumentException::class);
        $formatter = new Yaml($this->serializer->reveal());

        $formatter->decode($yaml);
    }
}
