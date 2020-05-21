<?php
namespace Acc\Core\PersistentData\Example\Foo\PDO\Request;

use Acc\Core\PersistentData\Example\Foo\EntityInterface;
use Acc\Core\PersistentData\PDO\PDOInterface;
use Acc\Core\PersistentData\RequestInterface;
use Acc\Core\PrinterInterface;

/**
 * Class Sync
 * Syncs an entity with persistent storage
 *
 * @package Acc\Core\PersistentData\Example\Foo\PDO\Request
 */
final class Sync implements RequestInterface
{
    /**
     * @var EntityInterface
     */
    private EntityInterface $entity;

    /**
     * @var RequestInterface
     */
    private RequestInterface $insert;

    /**
     * @var RequestInterface
     */
    private RequestInterface $update;

    /**
     * @var RequestInterface
     */
    private RequestInterface $resolved;

    /**
     * Sync constructor.
     * @param EntityInterface $entity
     * @param RequestInterface|null $insert
     * @param RequestInterface|null $update
     */
    public function __construct(
        EntityInterface $entity,
        RequestInterface $insert = null,
        RequestInterface $update = null
    ) {
        $this->entity = $entity;
        $this->insert = $insert;
        $this->update = $update;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        return $this->resolved()->printed($printer);
    }

    /**
     * @param PDOInterface $pdo
     * @return RequestInterface
     */
    public function executed(PDOInterface $pdo): RequestInterface
    {
        return $this->resolved()->executed($pdo);
    }

    /**
     * Resolves a target object that will be executing the contract
     * @return $this
     */
    private function resolved(): self
    {
        if ($this->resolved !== null) {
            return $this;
        }
        $obj = new self($this->entity, $this->insert, $this->update);
        if ($this->entity->options()->option('persisted', false)) {
            $obj->resolved = $this->update;
        } else {
            $obj->resolved = $this->insert;
        }
        return $obj;
    }
}
