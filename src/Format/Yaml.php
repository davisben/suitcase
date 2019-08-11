<?php

namespace Suitcase\Format;

use Ivory\Serializer\Format;
use Suitcase\Exception\FormatException;

class Yaml extends FormatBase
{
    /**
     * @inheritdoc
     */
    public function getExtension(): string
    {
        return '.yml';
    }

    /**
     * @inheritdoc
     */
    public function encode($array): string
    {
        try {
            $json = $this->serializer->serialize($array, Format::YAML);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error encoding data.', $e->getMessage());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public function decode($yaml): array
    {
        try {
            $array = $this->serializer->deserialize($yaml, 'array', Format::YAML);
        } catch (\InvalidArgumentException $e) {
            throw new FormatException('Error decoding data.', $e->getMessage());
        }

        return $array;
    }
}
