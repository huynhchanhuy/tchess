<?php

namespace Tchess\Rule;

interface MoveCheckerInterface
{
    /**
     * Returns an array of rules which is callbacks to check the move.
     *
     * A rule is a array of callback and the priority (defaults to 0).
     *
     * For instance:
     *
     *  * array('methodName')
     *  * array(array('methodName', $priority))
     *  * array(array('methodName1', $priority), 'methodName2')
     *
     * @return array The rules to check the move
     */
    public static function getRules();
}
