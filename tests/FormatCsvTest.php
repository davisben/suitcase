<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Ivory\Serializer\SerializerInterface;
use Ivory\Serializer\Format;
use Suitcase\Format\Csv;
use Suitcase\Exception\FormatException;

class FormatCsvTest extends TestCase
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
    public function csvDataProvider(): array
    {
        return [
            [
                [
                    'foo' => [
                        'bar' => 'baz',
                    ],
                ],
                "foo.bar\nbaz",
            ],
        ];
    }

    /**
     * Test that the proper file extension is returned.
     */
    public function testFileExtension(): void
    {
        $formatter = new Csv($this->serializer->reveal());
        $this->assertEquals('.csv', $formatter->getExtension());
    }

    /**
     * Test that data is properly encoded to JSON.
     *
     * @dataProvider csvDataProvider
     */
    public function testEncode($array, $csv): void
    {
        $this->serializer->serialize($array, Format::CSV)->willReturn($csv);
        $formatter = new Csv($this->serializer->reveal());

        $encoded = $formatter->encode($array);
        $this->assertEquals($csv, $encoded);
    }

    /**
     * Test that data is properly decoded from JSON.
     *
     * @dataProvider csvDataProvider
     */
    public function testDecode($array, $csv): void
    {
        $this->serializer->deserialize($csv, 'array', Format::CSV)->willReturn($array);
        $formatter = new Csv($this->serializer->reveal());

        $decoded = $formatter->decode($csv);
        $this->assertEquals($array, $decoded);
    }

    /**
     * Test that an invalid array throws an exception.
     */
    public function testInvalidEncode(): void
    {
        $this->expectException(FormatException::class);

        $array = ['foo' => chr(255)];
        $this->serializer->serialize($array, Format::CSV)->willThrow(\InvalidArgumentException::class);
        $formatter = new Csv($this->serializer->reveal());

        $formatter->encode($array);
    }

    /**
     * Test that invalid JSON throws an exception.
     */
    public function testInvalidDecode(): void
    {
        $this->expectException(FormatException::class);

        $csv = chr(255);
        $this->serializer->deserialize($csv, 'array', Format::CSV)->willThrow(\InvalidArgumentException::class);
        $formatter = new Csv($this->serializer->reveal());

        $formatter->decode($csv);
    }
}
