<?php
namespace Acc\Core\PersistentData\Example\Foo;

use Acc\Core\PersistentData\EntityInterface as EntityType;
use DateTimeImmutable;

/**
 * Interface EntityInterface
 * @package Acc\Core\PersistentData\Example\Foo
 */
interface EntityInterface extends EntityType
{
    /**
     * Appends info about id
     * @param int $id
     * @return EntityInterface
     */
    public function withId(int $id): EntityInterface;

    /**
     * Appends info about memo
     * @param string $memo
     * @return EntityInterface
     */
    public function withMemo(string $memo): EntityInterface;

    /**
     * Appends info about date of created
     * @param DateTimeImmutable $dt
     * @return EntityInterface
     */
    public function withCreated(DateTimeImmutable $dt): EntityInterface;

    /**
     * Appends info about date of changed
     * @param DateTimeImmutable|null $dt
     * @return EntityInterface
     */
    public function withUpdated(DateTimeImmutable $dt = null): EntityInterface;

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return EntityInterface
     */
    public function withOption(string $key, $val): EntityInterface;
}
