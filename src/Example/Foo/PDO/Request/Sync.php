<?php
/**
 * This file is part of the jigius/acc-core-pdata library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2020 Jigius <jigius@gmail.com>
 * @link https://github.com/jigius/acc-core-pdata GitHub
 */

declare(strict_types=1);

namespace Acc\Core\PersistentData\Example\Foo\PDO\Request;

use Acc\Core\PersistentData\Example\Foo\EntityInterface;
use Acc\Core\PersistentData\PDO\ExtendedPDOInterface;
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
     * @var RequestInterface|null
     */
    private ?RequestInterface $insert;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $update;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $resolved = null;

    /**
     * Sync constructor.
     * @param EntityInterface $entity
     * @param RequestInterface|null $insert
     * @param RequestInterface|null $update
     */
    public function __construct(
        EntityInterface $entity,
        ?RequestInterface $insert = null,
        ?RequestInterface $update = null
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
     * @inheritDoc
     */
    public function executed(ExtendedPDOInterface $pdo): RequestInterface
    {
        return $this->resolved()->resolved->executed($pdo);
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
            $obj->resolved = $this->update ?? new Update($this->entity);
        } else {
            $obj->resolved = $this->insert ?? new Insert($this->entity);
        }
        return $obj;
    }
}
