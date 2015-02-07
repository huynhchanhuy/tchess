<?php

namespace Tchess\Validator\Constraints;

use Tchess\Rule\MoveCheckerInterface;

interface MoveValidatorInterface
{

    /**
     * Adds rules to validator.
     */
    public function addRules(MoveCheckerInterface $checker);
}
