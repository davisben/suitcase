<?php

namespace Suitcase\Format;

use Ivory\Serializer\Format;
use Suitcase\Exception\FormatException;

class Json extends FormatBase
{
    /**
     * @inheritdoc
     */
    public function getExtension(): string
    {
        return '.json';
    }

    /**
     * @inheritdoc
     */
    public function encode($array): string
    {
        try {
            $json = $this->serializer->serialize($array, Format::JSON);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error encoding data.', $e->getMessage());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function decode($json): array
    {
        try {
            $array = $this->serializer->deserialize($json, 'array', Format::JSON);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error decoding data.', $e->getMessage());
        }

        return $array;
    }
}
