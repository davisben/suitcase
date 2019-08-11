<?php

namespace Suitcase\Format;

use Ivory\Serializer\Format;
use Suitcase\Exception\FormatException;

class Csv extends FormatBase
{
    /**
     * @inheritdoc
     */
    public function getExtension(): string
    {
        return '.csv';
    }

    /**
     * @inheritdoc
     */
    public function encode($array): string
    {
        try {
            $json = $this->serializer->serialize($array, Format::CSV);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error encoding data.', $e->getMessage());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function decode($csv): array
    {
        try {
            $array = $this->serializer->deserialize($csv, 'array', Format::CSV);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error decoding data.', $e->getMessage());
        }

        return $array;
    }
}
