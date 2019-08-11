<?php

namespace Suitcase\Format;

interface FormatInterface
{
    /**
     * Get the file extension to use.
     *
     * @return string
     */
    public function getExtension(): string;

    /**
     * Encode an array of data.
     *
     * @param array $array
     *   The data to encode.
     *
     * @return string
     *   The encoded data.
     *
     * @throws \Suitcase\Exception\FormatException
     *   Throws an exception if encoding fails.
     */
    public function encode($array): string;

    /**
     * Decode the data.
     *
     * @param string $data
     *   The data to decode.
     *
     * @return array
     *   An array of decoded data.
     *
     * @throws \Suitcase\Exception\FormatException
     *   Throws an exception if decoding fails.
     */
    public function decode($data): array;
}
