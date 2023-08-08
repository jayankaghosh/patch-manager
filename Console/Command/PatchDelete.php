<?php

namespace Jayanka\PatchManager\Console\Command;

use Jayanka\PatchManager\Model\PatchManagement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PatchDelete extends Command
{
    /**
     * @var PatchManagement
     */
    private $patchManagement;

    /**
     * @param PatchManagement $patchManagement
     * @param string|null $name
     */
    public function __construct(
        PatchManagement $patchManagement,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->patchManagement = $patchManagement;
    }

    protected function configure()
    {
        $this->setName('j:patch:delete')
            ->setDescription(__('Delete patches by its class name(s)'))
            ->setDefinition([
                new InputOption('className', 'C', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, __('The patch class names'))
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classNames = $input->getOption('className');
        foreach ($classNames as $className) {
            $output->writeln(__('Deleting patch <info>%1</info>', $className));
            $this->patchManagement->deletePatchByClassName($className);
        }
        $output->writeln(__('<info>Patches deleted successfully</info>'));
    }
}
