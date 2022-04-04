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

namespace Spipu\ConfigurationBundle\Command;

use Exception;
use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
{
    public const OPTION_KEY = 'key';
    public const OPTION_VALUE = 'value';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * ConfigurationCommand constructor.
     * @param Manager $manager
     * @param null|string $name
     */
    public function __construct(
        Manager $manager,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->manager = $manager;
    }

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('spipu:configuration:edit')
            ->setDescription('Edit a Spipu Configuration.')
            ->setHelp('This command allows you to edit a spipu configuration')
            ->addOption(
                static::OPTION_KEY,
                null,
                InputOption::VALUE_REQUIRED,
                'Key of the configuration to edit'
            )
            ->addOption(
                static::OPTION_VALUE,
                null,
                InputOption::VALUE_REQUIRED,
                'Value of the configuration to edit'
            );
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getOption(static::OPTION_KEY);
        $value = $input->getOption(static::OPTION_VALUE);

        $definition = $this->manager->getDefinition($key);
        if ($definition->getType() === 'file') {
            throw new ConfigurationException('Unable to set a file in CLI');
        }

        $output->writeln(sprintf('Update [%s]', $key));
        $this->manager->set($key, $value);
        $output->writeln(' => done');

        return self::SUCCESS;
    }
}
