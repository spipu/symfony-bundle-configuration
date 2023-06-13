<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Service\ScopeService;
use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
use Spipu\ConfigurationBundle\Ui\ConfigurationGrid;
use Spipu\ConfigurationBundle\Ui\Grid\DataProvider;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/configuration')]
class ConfigurationController extends AbstractController
{
    private ScopeService $scopeService;

    public function __construct(
        ScopeService $scopeService
    ) {
        $this->scopeService = $scopeService;
    }

    #[Route(path: '/list/{scopeCode}', name: 'spipu_configuration_admin_list', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW')]
    public function index(
        GridFactory $gridFactory,
        ConfigurationGrid $configurationGrid,
        string $scopeCode = ''
    ): Response {
        try {
            $scope = $this->scopeService->getScope($scopeCode);
        } catch (ConfigurationScopeException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $scopeCode = null;
        if ($scope) {
            $scopeCode = $scope->getCode();
        }
        $configurationGrid->setCurrentScope($scopeCode);

        $manager = $gridFactory->create($configurationGrid);
        $manager->setRoute('spipu_configuration_admin_list');

        /** @var DataProvider $dataProvider */
        $dataProvider = $manager->getDataProvider();
        $dataProvider->setCurrentScope($scopeCode);
        $manager->validate();

        return $this->render(
            '@SpipuConfiguration/index.html.twig',
            [
                'manager'      => $manager,
                'hasScopes'    => $this->scopeService->hasScopes(),
                'scopes'       => $this->scopeService->getSortedScopes(),
                'currentScope' => $scopeCode,
            ]
        );
    }

    #[Route(path: '/show/{code}/{scopeCode}', name: 'spipu_configuration_admin_edit', methods: 'GET|POST')]
    #[IsGranted('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT')]
    public function edit(
        FormFactory $formFactory,
        ConfigurationForm $configurationForm,
        string $code,
        ?string $scopeCode = null
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $scope = $this->scopeService->getScope($scopeCode);
        } catch (ConfigurationScopeException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $scopeCode = null;
        if ($scope) {
            $scopeCode = $scope->getCode();
        }

        $configurationForm->setConfigurationCode($code);

        $manager = $formFactory->create($configurationForm);
        $manager->setSubmitButton('spipu.ui.action.update');
        if ($manager->validate()) {
            return $this->redirectToRoute('spipu_configuration_admin_list', ['scopeCode' => $scopeCode]);
        }

        return $this->render(
            '@SpipuConfiguration/show.html.twig',
            [
                'manager'      => $manager,
                'currentScope' => $scopeCode,
            ]
        );
    }
}
