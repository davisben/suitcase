<?php

namespace Suitcase\Format;

use Suitcase\Exception\FormatException;

class Json implements FormatInterface
{
    /**
     * JSON file extension.
     */
    const FILE_EXT = '.json';

    /**
     * @inheritdoc
     */
    public static function encode($array): string
    {
        $options = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
        $json = json_encode($array, $options);

        if (!$json) {
            throw new FormatException('Error encoding data.', json_last_error_msg());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public static function decode($json): array
    {
        $array = json_decode($json, true);

        if ($array === null) {
            throw new FormatException('Error decoding data', json_last_error_msg());
        }

        return $array;
    }
}
