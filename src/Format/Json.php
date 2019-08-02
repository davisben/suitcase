<?php

namespace Suitcase\Format;

class Json implements FormatInterface
{
    /**
     * JSON file extension.
     */
    const FILE_EXT = '.json';

    /**
     * @inheritdoc
     */
    public static function encode($array)
    {
        $options = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
        $json = json_encode($array, $options);

        if (!$json) {
            throw new \Exception('Error encoding data: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * @inheritdoc
     */
    public static function decode($json)
    {
        $array = json_decode($json, true);

        if ($array === null) {
            throw new \Exception('Error decoding data: ' . json_last_error_msg());
        }

        return $array;
    }
}
