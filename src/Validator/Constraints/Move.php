<?php

namespace Tchess\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Move extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'validator.move';
    }
}
