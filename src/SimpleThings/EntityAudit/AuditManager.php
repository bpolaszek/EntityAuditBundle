<?php

namespace SimpleThings\EntityAudit;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use SimpleThings\EntityAudit\EventListener\CreateSchemaListener;
use SimpleThings\EntityAudit\EventListener\LogRevisionsListener;
use SimpleThings\EntityAudit\Metadata\MetadataFactory;

/**
 * Audit Manager grants access to metadata and configuration
 * and has a factory method for audit queries.
 */
class AuditManager
{
    /**
     * @var AuditConfiguration
     */
    private $config;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param AuditConfiguration $config
     */
    public function __construct(EntityManager $entityManager, AuditConfiguration $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->metadataFactory = new Metadata\MetadataFactory($this->entityManager, $config->getMetadataDriver());

        $this->registerEvents($entityManager->getEventManager());
    }

    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function createAuditReader(array $options = [])
    {
        return new AuditReader($this->entityManager, $this->config, $this->metadataFactory, $options);
    }

    protected function registerEvents(EventManager $evm)
    {
        $evm->addEventSubscriber(new CreateSchemaListener($this));
        $evm->addEventSubscriber(new LogRevisionsListener($this));
    }

    /**
     * @param EntityManager $entityManager
     * @return AuditManager
     */
    public static function create(EntityManager $entityManager)
    {
        return new self($entityManager, AuditConfiguration::createWithAnnotationDriver());
    }
}
