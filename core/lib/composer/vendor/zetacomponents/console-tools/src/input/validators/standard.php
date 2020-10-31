<?php
/**
 * File containing the ezcConsoleStandardInputValidator class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package ConsoleTools
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 */
/**
 * Validates ezcConsoleOption in terms of dependencies/exclusion and more.
 * 
 * @package ConsoleTools
 * @version //autogen//
 *
 * @access private
 */
class ezcConsoleStandardInputValidator implements ezcConsoleInputValidator
{
    /**
     * Validates the given $options and $arguments.
     *
     * Validates the given $options against their registered rules. Throws an 
     * exception, if any condition is not met. $hasArguments indicates if 
     * arguments have been submitted in addition.
     *
     * @param array(ezcConsoleOption) $options
     * @param bool $hasArguments
     *
     * @throws ezcConsoleOptionDependencyViolationException
     *         If a dependency was violated. 
     * @throws ezcConsoleOptionExclusionViolationException 
     *         If an exclusion rule was violated.
     * @throws ezcConsoleOptionArgumentsViolationException 
     *         If arguments are passed although a parameter dissallowed them.
     * @throws ezcConsoleOptionMandatoryViolationException
     *         If an option that was marked mandatory was not submitted.
     * @throws ezcConsoleOptionMissingValueException
     *         If an option that expects a value was submitted without one.
     */
    public function validateOptions( array $options, $hasArguments )
    {
        foreach ( $options as $id => $option )
        {
            if ( $option->mandatory === true && $option->value === false )
            {
                throw new ezcConsoleOptionMandatoryViolationException( $option );
            }

            $this->validateDependencies( $option );
            $this->validateExclusions( $option );

            if ( $option->arguments === false && $option->value !== false && $hasArguments )
            {
                throw new ezcConsoleOptionArgumentsViolationException( $option );
            }
        }
    }

    /**
     * Validated option dependencies.
     *
     * Validates dependencies by $option.
     *
     * @param ezcConsoleOption $option.
     */
    private function validateDependencies( ezcConsoleOption $option )
    {
        $optSet = ( $option->value !== false
            && ( !is_array( $option->value ) || $option->value !== array() ) );

        foreach ( $option->getDependencies() as $dep )
        {
            if ( $dep->ifSet === $optSet )
            {
                $this->validateDependency( $option, $dep );
            }
        }
    }

    /**
     * Validates a single dependency.
     *
     * Validates the dependency $dep, which is set in the $srcOpt.
     *
     * @param ezcConsoleOption $srcOpt
     * @param ezcConsoleOptionRule $dep
     */
    private function validateDependency( ezcConsoleOption $srcOpt, ezcConsoleOptionRule $dep )
    {
        $optValue = $dep->option->value;

        if ( $optValue === false || $optValue === array() )
        {
            throw new ezcConsoleOptionDependencyViolationException(
                $srcOpt,
                $dep->option
            );
        }

        if ( $dep->values !== array() )
        {
            $optVals = ( is_array( $optValue ) ? $optValue : array( $optValue) );
            $unrecognizedVals = array_diff( $optVals, $dep->values );
            if ( $unrecognizedVals !== array() )
            {
                throw new ezcConsoleOptionDependencyViolationException(
                    $srcOpt,
                    $dep->option,
                    implode( ', ', $dep->values )
                );
            }
        }
    }

    /**
     * Validated option exclusions.
     *
     * Validates exclusions by $option.
     *
     * @param ezcConsoleOption $option.
     */
    private function validateExclusions( ezcConsoleOption $option )
    {
        $optSet = ( $option->value !== false
            && ( !is_array( $option->value ) || $option->value !== array() ) );

        foreach ( $option->getExclusions() as $excl )
        {
            if ( $excl->ifSet === $optSet )
            {
                $this->validateExclusion( $option, $excl );
            }
        }
    }

    /**
     * Validates a single exclusion.
     *
     * Validates the exclusion $excl, which is set in the $srcOpt.
     *
     * @param ezcConsoleOption $srcOpt
     * @param ezcConsoleOptionRule $excl
     */
    private function validateExclusion( ezcConsoleOption $srcOpt, ezcConsoleOptionRule $excl )
    {
        $optValue = $excl->option->value;

        if ( $optValue !== false && $optValue !== array() && $excl->values === array() )
        {
            throw new ezcConsoleOptionExclusionViolationException(
                $srcOpt,
                $excl->option
            );
        }

        $optVals = ( is_array( $optValue ) ? $optValue : array( $optValue ) );
        $forbiddenVals = array_intersect( $optVals, $excl->values );
        if ( $forbiddenVals !== array() )
        {
            throw new ezcConsoleOptionExclusionViolationException(
                $srcOpt,
                $excl->option,
                implode( ', ', $excl->values )
            );
        }
    }
}

?>
