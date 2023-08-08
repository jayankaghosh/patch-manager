<?php

namespace Jayanka\PatchManager\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Setup\Exception;
use Magento\Framework\Setup\Exception as SetupException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchHistory;

class PatchManagement
{
    /**
     * @var PatchApplier
     */
    private $patchApplier;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @param ModuleListInterface $moduleList
     * @param PatchApplier $patchApplier
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ObjectManager $objectManager
     * @param PatchHistory $patchHistory
     */
    public function __construct(
        ModuleListInterface $moduleList,
        PatchApplier $patchApplier,
        ModuleDataSetupInterface $moduleDataSetup,
        ObjectManager $objectManager,
        PatchHistory $patchHistory
    )
    {
        $this->moduleList = $moduleList;
        $this->patchApplier = $patchApplier;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->objectManager = $objectManager;
        $this->patchHistory = $patchHistory;
    }

    /**
     * @param string $module
     * @return void
     * @throws NotFoundException
     * @throws Exception
     */
    public function applyByModule($module)
    {
        if (!$this->isModuleExists($module)) {
            throw new NotFoundException(__('Module with name %1 does not exist', $module));
        }
        $this->patchApplier->applyDataPatch($module);
    }

    public function applyByClassName($className)
    {
        $dataPatch = $this->objectManager->create(
            '\\' . $className,
            ['moduleDataSetup' => $this->moduleDataSetup]
        );
        if (!$dataPatch instanceof DataPatchInterface) {
            throw new SetupException(
                __("Patch %1 should implement DataPatchInterface", [get_class($dataPatch)])
            );
        }
        if ($dataPatch instanceof NonTransactionableInterface) {
            $dataPatch->apply();
            $this->patchHistory->fixPatch(get_class($dataPatch));
        } else {
            try {
                $this->moduleDataSetup->getConnection()->beginTransaction();
                $dataPatch->apply();
                $this->patchHistory->fixPatch(get_class($dataPatch));
                foreach ($dataPatch->getAliases() as $patchAlias) {
                    $this->patchHistory->fixPatch($patchAlias);
                }
                $this->moduleDataSetup->getConnection()->commit();
            } catch (\Exception $e) {
                $this->moduleDataSetup->getConnection()->rollBack();
                throw new SetupException(
                    new Phrase(
                        'Unable to apply data patch %1. Original exception message: %2',
                        [
                            get_class($dataPatch),
                            $e->getMessage()
                        ]
                    ),
                    $e
                );
            }
        }
    }

    public function deletePatchByClassName($className)
    {
        $this->patchHistory->revertPatchFromHistory($className);
    }


    /**
     * @param $moduleName
     * @return bool
     */
    private function isModuleExists($moduleName)
    {
        return $this->moduleList->getOne($moduleName) !== null;
    }
}
