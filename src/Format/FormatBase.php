<?php

namespace Suitcase\Format;

use Ivory\Serializer\SerializerInterface;

abstract class FormatBase implements FormatInterface
{
    /**
     * The serializer used to encode and decode data.
     *
     * @var \Ivory\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructs a formatter object.
     *
     * @param \Ivory\Serializer\SerializerInterface $serializer
     *   The serializer used to encode and decode data.
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
