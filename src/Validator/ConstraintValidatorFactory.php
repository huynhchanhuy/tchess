<?php

/*
 * This file is a clone of Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory.
 */

namespace Tchess\Validator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    protected $container;
    protected $validators;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container  The service container
     * @param array              $validators An array of validators
     */
    public function __construct(ContainerInterface $container, array $validators = array())
    {
        $this->container = $container;
        $this->validators = $validators;
    }

    /**
     * Returns the validator for the supplied constraint.
     *
     * @param Constraint $constraint A constraint
     *
     * @return ConstraintValidatorInterface A validator for the supplied constraint
     *
     * @throws UnexpectedTypeException When the validator is not an instance of ConstraintValidatorInterface
     */
    public function getInstance(Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if (!isset($this->validators[$name])) {
            $this->validators[$name] = new $name();
        } elseif (is_string($this->validators[$name])) {
            // $this->validators[$name] = $this->container->get($this->validators[$name]);
            // @hack - The service with prototype scope is created only one time,
            // so that only one instance will be returned. This hack will return
            // new instance of the service every time it is requested.
            return $this->container->get($this->validators[$name]);
        }

        if (!$this->validators[$name] instanceof ConstraintValidatorInterface) {
            throw new UnexpectedTypeException($this->validators[$name], 'Symfony\Component\Validator\ConstraintValidatorInterface');
        }

        return $this->validators[$name];
    }
}
