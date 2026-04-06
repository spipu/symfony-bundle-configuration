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

use Spipu\ConfigurationBundle\Service\ScopeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spipu:configuration:scope', description: 'Show Spipu Configuration scopes.')]
class ScopeCommand extends Command
{
    private ScopeService $scopeService;

    public function __construct(
        ScopeService $scopeService,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->scopeService = $scopeService;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command shows you all the configuration scopes')
        ;
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Is using scopes: ' . ($this->scopeService->hasScopes() ? 'Yes' : 'No'));
        $output->writeln('');
        $output->writeln('Scopes');
        $output->writeln(' - Global');
        foreach ($this->scopeService->getScopes() as $scope) {
            $output->writeln(sprintf(' - [%s] %s', $scope->getCode(), $scope->getName()));
        }
        $output->writeln('');

        return self::SUCCESS;
    }
}
