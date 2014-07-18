<?php

namespace Tchess;

use Tchess\FrameworkInterface;

interface FrameworkAwareInterface
{
    public function setFramework(FrameworkInterface $framework = null);
}
