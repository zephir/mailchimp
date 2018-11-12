<?php

namespace Sup7even\Mailchimp\Controller;

use Sup7even\Mailchimp\Domain\Model\Dto\FormDto;
use Sup7even\Mailchimp\Exception\GeneralException;
use Sup7even\Mailchimp\Exception\MemberExistsException;
use Sup7even\Mailchimp\Service\ApiService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FormController extends ActionController
{
    /** @var ApiService $service */
    protected $registrationService;

    public function initializeAction()
    {
        $this->registrationService = GeneralUtility::makeInstance(ApiService::class);
    }

    /**
     * @dontvalidate $form
     */
    public function indexAction(FormDto $form = null)
    {
        if (is_null($form)) {
            $form = GeneralUtility::makeInstance(FormDto::class);
            $prefill = GeneralUtility::_GP('email');
            if ($prefill) {
                $form->setEmail($prefill);
            }
        }

        if ($this->settings['interestId']) {
            $interests = $this->registrationService->getCategories($this->settings['listId'], $this->settings['interestId']);
        } else {
            $interests = [];
        }
        $this->view->assignMultiple([
            'form' => $form,
            'interests' => $interests,
            'data' => $this->configurationManager->getContentObject()->data
        ]);
    }

    /**
     * @param FormDto $form
     * @dontvalidate $form
     */
    public function ajaxResponseAction(FormDto $form = null)
    {
        $this->handleRegistration($form);
    }

    /**
     * @param FormDto $form
     */
    public function responseAction(FormDto $form = null)
    {
        if (is_null($form)) {
            $this->redirect('index');
        }
        if ($this->request->hasArgument('contentid')) {
            if (intval($this->request->getArgument('contentid')) === intval($this->configurationManager->getContentObject()->data['uid'])) {
                $this->handleRegistration($form);
            } else {
                return '';
            }
        } else {
            $this->handleRegistration($form);
        }
    }

    /**
     * @param FormDto|null $form
     */
    protected function handleRegistration(FormDto $form = null)
    {
        try {
            $this->registrationService->register($this->settings['listId'], $form);
        } catch (MemberExistsException $e) {
            $this->view->assign('error', 'memberExists');
        } catch (GeneralException $e) {
            $this->view->assign('error', 'general');
        }

        $this->view->assignMultiple([
            'form' => $form
        ]);
    }
}
