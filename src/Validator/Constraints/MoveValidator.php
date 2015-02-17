<?php

namespace Tchess\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tchess\Entity\Piece\Move as MoveEntity;
use Tchess\Rule\MoveCheckerInterface;

class MoveValidator extends ConstraintValidator implements MoveValidatorInterface
{
    private $rules = array();

    /**
     * {@inheritdoc}
     */
    public function validate($move, Constraint $constraint)
    {
        if (!$constraint instanceof Move) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Move');
        }

        if (!$move instanceof MoveEntity) {
            throw new UnexpectedTypeException($move, 'Tchess\Entity\Piece\Move\Move');
        }

        krsort($this->rules);
        foreach ($this->rules as $priority => $rules) {
            foreach ($rules as $rule) {
                $message = call_user_func($rule, $move);
                if (!empty($message)) {
                    $this->buildViolation($message)
                        // Currently we do not support parameters in message.
                        //->setParameter('%string%', $value)
                        ->addViolation();
                    // Stop propagation.
                    break 2;
                }
            }
        }
    }

    /**
     * @see MoveValidatorInterface::addRules
     *
     * @api
     */
    public function addRules(MoveCheckerInterface $checker)
    {
        foreach ($checker->getRules() as $params) {
            if (is_string($params)) {
                $this->addRule(array($checker, $params));
            } elseif (is_string($params[0])) {
                $this->addRule(array($checker, $params[0]), isset($params[1]) ? $params[1] : 0);
            }
        }
    }

    protected function addRule($callback, $priority = 0)
    {
        $this->rules[$priority][] = $callback;
    }
}
