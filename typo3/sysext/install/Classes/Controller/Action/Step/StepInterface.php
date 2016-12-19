<?php
namespace TYPO3\CMS\Install\Controller\Action\Step;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Install\Controller\Action;

/**
 * Interface implemented by single steps
 */
interface StepInterface
{
    /**
     * Execute a step
     *
     * @return array<\TYPO3\CMS\Install\Status\StatusInterface>
     */
    public function execute();

    /**
     * Whether this step must be executed
     *
     * @return bool TRUE if this step needs to be executed
     */
    public function needsExecution();

    /**
     * Stores the context of install tool in specific action, standalone or backend
     *
     * @param string $context the current context
     * @return void
     */
    public function setContext($context);

    /**
     * Tell the action which position it has in the list of actions
     *
     * @param int $current The current position
     * @param int $total The total number of steps
     * @return void
     */
    public function setStepsCounter($current, $total);

    /**
     * Gets current position
     *
     * @return int
     */
    public function getCurrentStep();

    /**
     * Gets total steps
     *
     * @return int
     */
    public function getTotalSteps();

    /**
     * Marks step as being "done" so that it not shown again.
     *
     * Writes the info in LocalConfiguration.php
     *
     * @param string $stepName The install step
     * @param mixed $confValue The configuration is set to this value
     * @return void
     */
    public function markStepAsDone($stepAction = '', $confValue = 1);
}
