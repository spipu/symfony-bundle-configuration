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

class ShowCommand extends Command
{
    public const OPTION_KEY = 'key';
    public const OPTION_DIRECT = 'direct';

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
                static::OPTION_DIRECT,
                'd',
                InputOption::VALUE_NONE,
                'Display directly and only the value as output. To use only with a key'
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
        $direct = $input->getOption(static::OPTION_DIRECT);

        if ($key) {
            if ($direct) {
                $this->showOneDirect($output, $key);
                return self::SUCCESS;
            }
            $this->showOne($output, $key);
            return self::SUCCESS;
        }

        $this->showAll($output);

        return self::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param string $key
     * @return void
     * @throws ConfigurationException
     */
    protected function showOneDirect(OutputInterface $output, string $key): void
    {
        $value = $this->manager->get($key);
        if ($value === null) {
            $value = '';
        }
        if (is_bool($value)) {
            $value = (int) $value;
        }

        $output->writeln((string) $value);
    }

    /**
     * @param OutputInterface $output
     * @param string $key
     * @return void
     * @throws ConfigurationException
     */
    private function showOne(OutputInterface $output, string $key): void
    {
        $output->writeln(sprintf("Show Configuration [%s]", $key));
        $output->writeln("");

        $list = [
            $this->prepareItem(
                $this->manager->getDefinition($key),
                $this->manager->get($key)
            )
        ];

        $this->displayTable($output, $list);
    }

    /**
     * @param OutputInterface $output
     * @return void
     * @throws ConfigurationException
     */
    private function showAll(OutputInterface $output): void
    {
        $output->writeln("Show All Configurations");
        $output->writeln("");

        $definitions = $this->manager->getDefinitions();
        $list = [];
        foreach ($definitions as $definition) {
            $list[] = $this->prepareItem(
                $definition,
                $this->manager->get($definition->getCode())
            );
        }

        $this->displayTable($output, $list);
    }

    /**
     * @param Definition $definition
     * @param mixed $value
     * @return array
     */
    private function prepareItem(Definition $definition, $value): array
    {
        return [
            'code'     => $definition->getCode(),
            'type'     => $definition->getType(),
            'required' => $definition->isRequired() ? 'yes' : 'no',
            'value'    => $value,
        ];
    }

    /**
     * @param OutputInterface $output
     * @param array $values
     * @return void
     */
    private function displayTable(OutputInterface $output, array $values): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(array_keys($values[0]))
            ->setRows($values);
        $table->render();
    }
}
