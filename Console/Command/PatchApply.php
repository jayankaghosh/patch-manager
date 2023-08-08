<?php

namespace Jayanka\PatchManager\Console\Command;

use Jayanka\PatchManager\Model\PatchManagement;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Setup\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PatchApply extends Command
{


    /**
     * @var PatchManagement
     */
    private $patchManagement;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param PatchManagement $patchManagement
     * @param ModuleListInterface $moduleList
     * @param string|null $name
     */
    public function __construct(
        PatchManagement $patchManagement,
        ModuleListInterface $moduleList,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->patchManagement = $patchManagement;
        $this->moduleList = $moduleList;
    }

    protected function configure()
    {
        $this->setName('j:patch:apply')
            ->setDescription(__('Apply patches by module name(s) or class name(s)'))
            ->setDefinition([
                new InputOption('module', 'M', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, __('The affected modules')),
                new InputOption('className', 'C', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, __('The patch class names'))
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws NotFoundException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classNames = $input->getOption('className');
        $modules = $input->getOption('module');
        if (count($classNames)) {
            foreach ($classNames as $className) {
                $output->writeln(__('Processing patch class <info>%1</info>', $className));
                $this->patchManagement->applyByClassName($className);
            }
        } else {
            if (!count($modules)) {
                $modules = array_column($this->moduleList->getAll(), 'name');
            }

            foreach ($modules as $module) {
                $output->writeln(__('Processing module <info>%1</info>', $module));
                $this->patchManagement->applyByModule($module);
            }
        }
        $output->writeln('<info>Patches applied</info>');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
