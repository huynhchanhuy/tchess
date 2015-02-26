<?php

namespace Tchess\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Move extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'constraint_validator.move';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
