<?php

namespace Tchess\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class FenEncoder implements EncoderInterface, DecoderInterface
{
    const FORMAT = 'fen';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        $encoded = '';
        $empty_count = 0;
        $separator = '/';
        for ($x = 7; $x >= 0; $x--) {
            for ($y = 0; $y < 8; $y++) {
                if (empty($data[$x][$y])) {
                    $empty_count++;
                }
                else {
                    if ($empty_count > 0) {
                        $encoded .= $empty_count . $data[$x][$y];
                        // Reset empty count.
                        $empty_count = 0;
                    }
                    else {
                        $encoded .= $data[$x][$y];
                    }
                }
            }

            // Force print empty count.
            if ($empty_count > 0 && $empty_count <= 8) {
                $encoded .= $empty_count;
                // Reset empty count.
                $empty_count = 0;
            }

            // End of row.
            if ($x > 0) {
                $encoded .= $separator;
            }
        }
        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        $rows = explode('/', $data);
        $decoded = array();

        if (count($rows) != 8) {
            throw new \InvalidArgumentException("Invalid FEN format's data");
        }

        for($x = 0; $x < 8; $x++) {
            $y = 0;

            $row = $rows[$x];
            $strlen = strlen($row);
            for($i = 0; $i < $strlen; $i++) {
                $char = substr($row, $i, 1);

                if (is_numeric($char)) {
                    $empty_count = (int) $char;
                    if ($empty_count < 1 || $empty_count > 8) {
                        throw new \InvalidArgumentException("Invalid empty squares count");
                    }

                    for ($i2 = 0; $i2 < $empty_count; $i2++) {
                        $decoded[7 - $x][$y] = '';
                        $y++;
                    }
                }
                else {
                    if (!in_array(strtoupper($char), array('B', 'K', 'N', 'P', 'Q', 'R'))) {
                        throw new \InvalidArgumentException("Invalid piece character");
                    }

                    $decoded[7 - $x][$y] = $char;
                    $y++;
                }
            }

            if ($y > 8) {
                throw new \InvalidArgumentException("Row contains more than 8 piece");
            }
        }
        return $decoded;
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
