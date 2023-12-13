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

use Spipu\ConfigurationBundle\Service\ConfigurationManager as Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{
    public const OPTION_KEY = 'key';
    public const OPTION_SCOPE = 'scope';

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
            ->setName('spipu:configuration:delete')
            ->setDescription('Delete a Spipu Configuration.')
            ->setHelp('This command allows you to delete a spipu configuration, in order to use the default value')
            ->addOption(
                static::OPTION_KEY,
                null,
                InputOption::VALUE_REQUIRED,
                'Key of the configuration to delete'
            )
            ->addOption(
                static::OPTION_SCOPE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Scope of the configuration to delete. Global scope if empty'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = (string) $input->getOption(static::OPTION_KEY);
        $scope = $input->getOption(static::OPTION_SCOPE);
        if ($scope === null || $scope === 'global') {
            $scope = '';
        }

        $output->writeln('Delete Configuration');
        $output->writeln('  - Key:   ' . $key);
        $output->writeln('  - Scope: ' . ($scope === '' ? 'global' : $scope));
        $output->writeln('');

        $this->manager->delete($key, $scope);
        $output->writeln(' => done');

        return self::SUCCESS;
    }
}
