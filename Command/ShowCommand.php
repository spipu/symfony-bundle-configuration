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

use Spipu\ConfigurationBundle\Entity\Definition;
use Spipu\ConfigurationBundle\Service\ConfigurationManager as Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends Command
{
    public const OPTION_KEY = 'key';
    public const OPTION_SCOPE = 'scope';
    public const OPTION_DIRECT = 'direct';

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
            ->setName('spipu:configuration:show')
            ->setDescription('Show the Spipu Configuration.')
            ->setHelp('This command allows you to show the spipu configuration')
            ->addOption(
                static::OPTION_KEY,
                'k',
                InputOption::VALUE_OPTIONAL,
                'Key of the configuration to see (if empty, see all)'
            )
            ->addOption(
                static::OPTION_SCOPE,
                's',
                InputOption::VALUE_OPTIONAL,
                'Code of the scope to see. To use only with a key. (if empty, use global)'
            )
            ->addOption(
                static::OPTION_DIRECT,
                'd',
                InputOption::VALUE_NONE,
                'Display directly and only the value as output. To use only with a key'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getOption(static::OPTION_KEY);
        $direct = $input->getOption(static::OPTION_DIRECT);

        $scope = $input->getOption(static::OPTION_SCOPE);
        if ($scope === null || $scope === 'global') {
            $scope = '';
        }


        if ($key) {
            if ($direct) {
                $this->showOneDirect($output, $key, $scope);
                return self::SUCCESS;
            }
            $this->showOne($output, $key, $scope);
            return self::SUCCESS;
        }

        $this->showAll($output);

        return self::SUCCESS;
    }

    protected function showOneDirect(OutputInterface $output, string $key, string $scope): void
    {
        $value = $this->manager->get($key, $scope);
        if ($value === null) {
            $value = '';
        }

        $output->writeln((string) $value);
    }

    private function showOne(OutputInterface $output, string $key, string $scope): void
    {
        $output->writeln('Show Configuration');
        $output->writeln('  - Key:   ' . $key);
        $output->writeln('  - Scope: ' . ($scope === '' ? 'global' : $scope));
        $output->writeln('');

        $list = [
            $this->prepareItem(
                $this->manager->getDefinition($key),
                $this->manager->get($key, $scope)
            )
        ];

        $this->displayTable($output, $list);
    }

    private function showAll(OutputInterface $output): void
    {
        $output->writeln('Show All Configurations');
        $output->writeln('');

        $allValues = $this->manager->getAll();
        foreach ($allValues as $scopeCode => $values) {
            $scopeMessage = 'Values for scope [' . $scopeCode . ']';
            if ($scopeCode === 'default') {
                $scopeMessage = 'Default Values';
            }

            $list = [];
            foreach ($values as $key => $value) {
                $definition = $this->manager->getDefinition($key);
                $list[] = $this->prepareItem(
                    $definition,
                    $value
                );
            }

            if (count($list) === 0) {
                $list[] = ['empty' => 'empty'];
            }

            $output->writeln($scopeMessage);
            $this->displayTable($output, $list);
            $output->writeln('');
        }
    }

    private function prepareItem(Definition $definition, mixed $value): array
    {
        return [
            'code'     => $definition->getCode(),
            'type'     => $definition->getType(),
            'required' => $definition->isRequired() ? 'yes' : 'no',
            'scoped'   => $definition->isScoped() ? 'yes' : 'no',
            'value'    => $value,
        ];
    }

    private function displayTable(OutputInterface $output, array $values): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(array_keys($values[0]))
            ->setRows($values);
        $table->render();
    }
}
