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

use Spipu\ConfigurationBundle\Exception\ConfigurationScopeException;
use Spipu\ConfigurationBundle\Service\ScopeService;
use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
use Spipu\ConfigurationBundle\Ui\ConfigurationGrid;
use Spipu\ConfigurationBundle\Ui\Grid\DataProvider;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractConfigurationController extends AbstractController
{
    private ScopeService $scopeService;
    private GridFactory $gridFactory;
    private ConfigurationGrid $configurationGrid;
    private FormFactory $formFactory;
    private ConfigurationForm $configurationForm;
    protected array $routes = [];
    protected ?array $allowedCodes = null;

    public function __construct(
        ScopeService $scopeService,
        GridFactory $gridFactory,
        ConfigurationGrid $configurationGrid,
        FormFactory $formFactory,
        ConfigurationForm $configurationForm
    ) {
        $this->scopeService = $scopeService;
        $this->gridFactory = $gridFactory;
        $this->configurationGrid = $configurationGrid;
        $this->formFactory = $formFactory;
        $this->configurationForm = $configurationForm;
    }

    abstract protected function configureController(): void;

    protected function listAction(string $scopeCode = ''): Response
    {
        $this->configureController();
        $this->denyAccessUnlessGranted($this->routes['list']['role']);

        try {
            $scope = $this->scopeService->getScope($scopeCode);
        } catch (ConfigurationScopeException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        $scopeCode = null;
        if ($scope) {
            $scopeCode = $scope->getCode();
        }
        $this->configurationGrid->setShowNeededRole($this->routes['list']['role']);
        $this->configurationGrid->setCurrentScope($scopeCode);

        $manager = $this->gridFactory->create($this->configurationGrid);
        $manager->setRoute($this->routes['list']['name'], $this->routes['list']['params']);

        /** @var DataProvider $dataProvider */
        $dataProvider = $manager->getDataProvider();
        $dataProvider->setCurrentScope($scopeCode);
        $dataProvider->setAllowedCodes($this->allowedCodes);
        $manager->validate();

        return $this->render(
            $this->routes['list']['template'],
            [
                'manager'      => $manager,
                'hasScopes'    => $this->scopeService->hasScopes(),
                'scopes'       => $this->scopeService->getSortedScopes(),
                'currentScope' => $scopeCode,
            ]
        );
    }

    protected function editAction(string $code, ?string $scopeCode = null): Response
    {
        $this->configureController();
        $this->denyAccessUnlessGranted($this->routes['edit']['role']);
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

        if ($this->allowedCodes !== null && !in_array($code, $this->allowedCodes)) {
            throw $this->createNotFoundException('code not allowed');
        }

        $this->configurationForm->setConfigurationCode($code);

        $manager = $this->formFactory->create($this->configurationForm);
        $manager->setSubmitButton('spipu.ui.action.update');
        if ($manager->validate()) {
            return $this->redirectToRoute(
                $this->routes['list']['name'],
                $this->routes['list']['params'] + ['scopeCode' => $scopeCode]
            );
        }

        return $this->render(
            $this->routes['edit']['template'],
            [
                'manager'      => $manager,
                'currentScope' => $scopeCode,
            ]
        );
    }
}
