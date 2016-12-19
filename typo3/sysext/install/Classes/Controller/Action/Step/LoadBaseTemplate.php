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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Status\StatusUtility;
use TYPO3\CMS\Install\SystemEnvironment\Check;
use TYPO3\CMS\Install\SystemEnvironment\SetupCheck;

/**
 * 
 * Loads the base template with first form in FE
 */
class LoadBaseTemplate extends AbstractStepAction
{
    /**
     * Step needs to be executed if database connection is not successful.
     *
     * @throws \TYPO3\CMS\Install\Controller\Exception\RedirectException
     * @return bool
     */
    public function needsExecution()
    {
        return true;
    }

    /**
     * Execute load base template action:
     *
     * @return array<\TYPO3\CMS\Install\Status\StatusInterface>
     */
    public function execute()
    {
        
    }
    
    /**
     * Executes the action
     *
     * @return string Rendered content
     */
    protected function executeAction()
    {

        
        if (@is_dir(PATH_typo3conf)) {
            /** @var \TYPO3\CMS\Core\Configuration\ConfigurationManager $configurationManager */
            $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ConfigurationManager::class);
            $steps = $configurationManager->getLocalConfigurationValueByPath('INSTALL/stepDone');
            
            if(count($steps) > 0 ) {
//                end($steps);
                $lastSuccessfulStep = key($steps);
            } else {
                $lastSuccessfulStep = 'loadBaseTemplate';    
            }
            
            $this->view->assign('step', $lastSuccessfulStep);

//            $steps = unserialize($steps);
//            foreach ($steps as $key => $val) {
//                if($val === 0) {
//                    $this->view->assign('step', $lastSuccessfulStep);
//                    break;
//                }
//                $lastSuccessfulStep = $key;
//            }
            
        }
        
        $this->assignSteps();
        return $this->view->render();
    }
}
