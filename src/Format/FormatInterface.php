<?php

namespace Suitcase\Format;

interface FormatInterface
{
    /**
     * Encode an array of data.
     *
     * @param array $array
     *   The data to encode.
     *
     * @return string
     *   The encoded data.
     *
     * @throws \Exception
     *   Throws an exception if encoding fails.
     */
    public static function encode($array);

    /**
     * Decode the data.
     *
     * @param string $data
     *   The data to decode.
     *
     * @return array
     *   An array of decoded data.
     *
     * @throws \Exception
     *   Throws an exception if decoding fails.
     */
    public static function decode($data);
}
