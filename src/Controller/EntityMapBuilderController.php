<?php
/**
 * @link    https://github.com/nnx-framework/doctrine
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace Nnx\Doctrine\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Console\Request;
use Interop\Container\ContainerInterface;
use Zend\View\Model\ConsoleModel;
use Nnx\Doctrine\Utils\EntityMapCacheInterface;

/**
 * Class EntityMapBuilderController
 *
 * @package Nnx\Doctrine\Controller
 */
class EntityMapBuilderController extends AbstractConsoleController
{
    /**
     * Менеджер для получения ObjectManager Doctrine2
     *
     * @var ContainerInterface
     */
    protected $doctrineObjectManager;

    /**
     * Сервис для кеширования EntityMap
     *
     * @var EntityMapCacheInterface
     */
    protected $entityMapCache;

    /**
     * EntityMapBuilderController constructor.
     *
     * @param ContainerInterface      $doctrineObjectManager
     * @param EntityMapCacheInterface $entityMapCache
     */
    public function __construct(ContainerInterface $doctrineObjectManager, EntityMapCacheInterface $entityMapCache)
    {
        $this->setDoctrineObjectManager($doctrineObjectManager);
        $this->setEntityMapCache($entityMapCache);
    }


    /**
     * Генерация карты сущностей и сохранение ее в кеше
     *
     * @return array
     */
    public function buildAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $managerName = $request->getParam('objectManager', 'doctrine.entitymanager.orm_default');

        if (!$this->getDoctrineObjectManager()->has($managerName)) {
            return [
                ConsoleModel::RESULT => sprintf('Doctrine ObjectManager %s not found', $managerName)
            ];
        }

        $entityMapCache = $this->getEntityMapCache();
        if ($entityMapCache->hasEntityMap($managerName)) {
            $entityMapCache->deleteEntityMap($managerName);
        }
        $entityMapCache->saveEntityMap($managerName);


        $entityMap = $entityMapCache->loadEntityMap($managerName);

        if (is_array($entityMap)) {
            $result = "Entity map:\n";
            foreach ($entityMap as $interfaceName => $className) {
                $result .= sprintf("Interface  name: %s. Class name: %s \n", $interfaceName, $className);
            }
        } else {
            $result = 'Empty entity map';
        }


        return [
            ConsoleModel::RESULT => $result
        ];
    }


    /**
     * Очистка карты сущностей из кеша
     *
     * @return array
     */
    public function clearAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $managerName = $request->getParam('objectManager', 'doctrine.entitymanager.orm_default');

        $entityMapCache = $this->getEntityMapCache();
        $result = "Entity map not found\n";
        if ($entityMapCache->hasEntityMap($managerName)) {
            $entityMapCache->deleteEntityMap($managerName);
            $result = "Entity map clear\n";
        }

        return [
            ConsoleModel::RESULT => $result
        ];
    }


    /**
     * Возвращает менеджер для получения ObjectManager Doctrine2
     *
     * @return ContainerInterface
     */
    public function getDoctrineObjectManager()
    {
        return $this->doctrineObjectManager;
    }

    /**
     * Устанавливает менеджер для получения ObjectManager Doctrine2
     *
     * @param ContainerInterface $doctrineObjectManager
     *
     * @return $this
     */
    public function setDoctrineObjectManager(ContainerInterface $doctrineObjectManager)
    {
        $this->doctrineObjectManager = $doctrineObjectManager;

        return $this;
    }

    /**
     * Возвращает сервис для кеширования EntityMap
     *
     * @return EntityMapCacheInterface
     */
    public function getEntityMapCache()
    {
        return $this->entityMapCache;
    }

    /**
     * Устанавливает сервис для кеширования EntityMap
     *
     * @param EntityMapCacheInterface $entityMapCache
     *
     * @return $this
     */
    public function setEntityMapCache(EntityMapCacheInterface $entityMapCache)
    {
        $this->entityMapCache = $entityMapCache;

        return $this;
    }
}
