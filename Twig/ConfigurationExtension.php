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

namespace Spipu\ConfigurationBundle\Twig;

use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\ConfigurationManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConfigurationExtension extends AbstractExtension
{
    private ConfigurationManager $configurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('get_config', [$this, 'getValue']),
            new TwigFilter('get_config_file_url', [$this, 'getFileUrl']),
        ];
    }

    public function getValue(string $key, ?string $scope = null): mixed
    {
        return $this->configurationManager->get($key, $scope);
    }

    public function getFileUrl(string $key, ?string $scope = null): ?string
    {
        return $this->configurationManager->getFileUrl($key, $scope);
    }
}
