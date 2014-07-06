<?php

namespace Tchess\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class BoardStringEncoder implements EncoderInterface, DecoderInterface
{
    const FORMAT = 'board_string';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        $rows = array();
        for ($x = 0; $x < 8; $x++) {
            $row = implode(' ', $data[$x]);
            $rows[] = $row;
        }
        return implode(' ', $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        $pieces = array();
        $items = explode(' ', $data);
        $index = 0;
        for ($x = 0; $x < 8; $x++) {
            for ($y = 0; $y < 8; $y++) {
                $pieces[$x][$y] = $items[$index];
                $index++;
            }
        }
        return $pieces;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }
}
