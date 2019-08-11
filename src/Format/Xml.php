<?php

namespace Suitcase\Format;

use Ivory\Serializer\Format;
use Suitcase\Exception\FormatException;

class Xml extends FormatBase
{
    /**
     * @inheritdoc
     */
    public function getExtension(): string
    {
        return '.xml';
    }

    /**
     * @inheritdoc
     */
    public function encode($array): string
    {
        try {
            $json = $this->serializer->serialize($array, Format::XML);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error encoding data.', $e->getMessage());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function decode($xml): array
    {
        try {
            $array = $this->serializer->deserialize($xml, 'array', Format::XML);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error decoding data.', $e->getMessage());
        }

        return $array;
    }
}
