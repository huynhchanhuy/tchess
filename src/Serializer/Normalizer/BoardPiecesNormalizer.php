<?php

namespace Tchess\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Tchess\Entity\Board;

class BoardPiecesNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof NormalizableInterface) {
            return $object->normalize($this, $format, $context);
        }
        else {
            return array();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $object = new Board();
        if ($object instanceof DenormalizableInterface) {
            $object->denormalize($this, $data, $format, $context);
        }
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof Board;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (!is_array($data) || count($data) != 8) {
            return false;
        }

        for($i = 0; $i < 8; $i++) {
            if (!is_array($data[$i]) || count($data[$i]) != 8) {
                return false;
            }
        }

        return true;
    }
}
