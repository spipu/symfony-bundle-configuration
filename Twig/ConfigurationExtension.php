<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Twig;

use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\Manager as ConfigurationManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConfigurationExtension extends AbstractExtension
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * ConfigurationExtension constructor.
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('get_config', [$this, 'getConfiguration']),
        ];
    }

    /**
     * @param string $key
     * @return mixed
     * @throws ConfigurationException
     */
    public function getConfiguration(string $key)
    {
        return $this->configurationManager->get($key);
    }
}
