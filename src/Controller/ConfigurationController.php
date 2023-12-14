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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/configuration')]
class ConfigurationController extends AbstractConfigurationController
{
    protected function configureController(): void
    {
        $this->routes = [
            'list'  => [
                'name'     => 'spipu_configuration_admin_list',
                'params'   => [],
                'role'     => 'ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW',
                'template' => '@SpipuConfiguration/index.html.twig',
            ],
            'edit'  => [
                'name'     => 'spipu_configuration_admin_edit',
                'params'   => [],
                'role'     => 'ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT',
                'template' => '@SpipuConfiguration/show.html.twig',
            ],
        ];

        $this->menuCurrentItem = 'spipu-configuration-admin';

        $this->allowedCodes = null;
    }

    #[Route(path: '/list/{scopeCode}', name: 'spipu_configuration_admin_list', methods: 'GET')]
    public function index(string $scopeCode = ''): Response
    {
        return $this->listAction($scopeCode);
    }

    #[Route(path: '/show/{code}/{scopeCode}', name: 'spipu_configuration_admin_edit', methods: 'GET|POST')]
    public function edit(string $code, ?string $scopeCode = null): Response
    {
        return $this->editAction($code, $scopeCode);
    }
}
