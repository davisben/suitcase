<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Ivory\Serializer\SerializerInterface;
use Ivory\Serializer\Format;
use Suitcase\Format\Xml;
use Suitcase\Exception\FormatException;

class FormatXmlTest extends TestCase
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
    public function xmlDataProvider(): array
    {
        return [
            [
                [
                    'foo' => [
                        'bar' => 'baz',
                    ],
                ],
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?><result><foo><bar>baz</bar></foo></result>",
            ],
        ];
    }

    /**
     * Test that the proper file extension is returned.
     */
    public function testFileExtension(): void
    {
        $formatter = new Xml($this->serializer->reveal());
        $this->assertEquals('.xml', $formatter->getExtension());
    }

    /**
     * Test that data is properly encoded to JSON.
     *
     * @dataProvider xmlDataProvider
     */
    public function testEncode($array, $xml): void
    {
        $this->serializer->serialize($array, Format::XML)->willReturn($xml);
        $formatter = new Xml($this->serializer->reveal());

        $encoded = $formatter->encode($array);
        $this->assertEquals($xml, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     *
     * @dataProvider xmlDataProvider
     */
    public function testDecode($array, $xml): void
    {
        $this->serializer->deserialize($xml, 'array', Format::XML)->willReturn($array);
        $formatter = new Xml($this->serializer->reveal());

        $decoded = $formatter->decode($xml);
        $this->assertEquals($array, $decoded);
    }

    /**
     * Test that an invalid array throws an exception.
     */
    public function testInvalidEncode(): void
    {
        $this->expectException(FormatException::class);

        $array = ['foo' => chr(255)];
        $this->serializer->serialize($array, Format::XML)->willThrow(\InvalidArgumentException::class);
        $formatter = new Xml($this->serializer->reveal());

        $formatter->encode($array);
    }

    /**
     * Test that invalid JSON throws an exception.
     */
    public function testInvalidDecode(): void
    {
        $this->expectException(FormatException::class);

        $xml = ":foo:\n\tbar: baz";
        $this->serializer->deserialize($xml, 'array', Format::XML)->willThrow(\InvalidArgumentException::class);
        $formatter = new Xml($this->serializer->reveal());

        $formatter->decode($xml);
    }
}
