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
use Spipu\ConfigurationBundle\Exception\ConfigurationException;
use Spipu\ConfigurationBundle\Service\ConfigurationManager as Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
{
    public const OPTION_KEY = 'key';
    public const OPTION_SCOPE = 'scope';
    public const OPTION_VALUE = 'value';

    private Manager $manager;

    public function __construct(
        Manager $manager,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->manager = $manager;
    }

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
                static::OPTION_SCOPE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Scope of the configuration to edit. Global scope if empty'
            )
            ->addOption(
                static::OPTION_VALUE,
                null,
                InputOption::VALUE_REQUIRED,
                'Value of the configuration to edit'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = (string) $input->getOption(static::OPTION_KEY);
        $value = (string) $input->getOption(static::OPTION_VALUE);
        $scope = $input->getOption(static::OPTION_SCOPE);
        if ($scope === null || $scope === 'global') {
            $scope = '';
        }

        $output->writeln('Edit Configuration');
        $output->writeln('  - Key:   ' . $key);
        $output->writeln('  - Scope: ' . ($scope === '' ? 'global' : $scope));
        $output->writeln('');

        $definition = $this->manager->getDefinition($key);
        if ($definition->getType() === 'file') {
            throw new ConfigurationException('Unable to set a file in CLI');
        }

        $this->manager->set($key, $value, $scope);
        $output->writeln(' => done');

        return self::SUCCESS;
    }
}
