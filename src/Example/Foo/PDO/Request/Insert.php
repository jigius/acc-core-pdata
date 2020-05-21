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
use Acc\Core\PersistentData\PDO\PDOInterface;
use Acc\Core\PersistentData\PDO\Vendor\PDOStatementInterface;
use Acc\Core\PersistentData\RequestInterface;
use Acc\Core\PrinterInterface;
use DomainException;
use LogicException;

/**
 * Class Insert
 * Inserts an entity into persistent storage
 * @package Acc\Core\PersistentData\Example\Foo\PDO\Request
 */
final class Insert implements RequestInterface, PrinterInterface
{
    /**
     * @var EntityInterface
     */
    private EntityInterface $entity;

    /**
     * Executed statement
     * @var PDOStatementInterface|null
     */
    private PDOStatementInterface $stmt;

    /**
     * @var array An input data
     */
    private array $i;

    /**
     * @var array|null A prepared data for printing
     */
    private array $o;

    /**
     * Insert constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return $this
     * @throws LogicException
     */
    public function with(string $key, $val): self
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = new self($this->entity);
        $obj->i = $this->i;
        $obj->i[$key] = $val;
        return $obj;
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws LogicException
     */
    public function finished(): self
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = new self($this->entity);
        $obj->o = $this->i;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        if ($this->o === null) {
            return $this->entity->printed($this)->printed($printer);
        }
        return
            $printer
                ->with('input', $this->i)
                ->with('query', $this->sqlStatement())
                ->with('values', $this->values())
                ->with('statement', $this->stmt)
                ->finished();
    }

    /**
     * @inheritDoc
     * @param PDOInterface $pdo
     * @return RequestInterface
     * @throws DomainException
     */
    public function executed(PDOInterface $pdo): RequestInterface
    {
        if ($this->o === null) {
            return $this->entity->printed($this)->executed($pdo);
        }
        $query = $this->sqlStatement();
        if (empty($query)) {
            return $this;
        }
        $this->validate();
        $obj = new self($this->entity);
        $obj->stmt = $pdo->query($query, $this->values());
        return $obj;
    }

    /**
     * Validates an input data
     * @throws DomainException
     */
    private function validate(): void
    {
        if (array_reduce(
                ['memo'],
                function ($carry, $key) {
                    return $carry && !isset($this->i[$key]);
                },
                true
            ) === false
        ) {
            throw new DomainException("value(s) are needed for mandatory params");
        }
        if ($this->entity->options()->option('persisted', false) === true) {
            throw new DomainException("the operation for this entity is prohibited");
        }
    }

    /**
     * @param string $key
     * @param mixed|null $defined
     * @param mixed|null $undefined
     * @param mixed|null $unknown
     * @return mixed|null
     */
    private function v3(
        string $key,
        $defined = null,
        $undefined = null,
        $unknown = null
    ) {
        if (!array_key_exists($key, $this->o)) {
            return $unknown;
        }
        return
            $this->o[$key] !== null?
                (
                    $defined === null? $this->o[$key]: $defined
                ):
                (
                    $undefined === null? $defined: $undefined
                );
    }

    /**
     * Query statement
     * @return string
     */
    private function sqlStatement(): string
    {
        $chunks = [
            "INSERT INTO `foo`",
            "(",
                implode(
                    ",",
                    array_filter(
                        [
                            $this->v3('id', "`id`"),
                            $this->v3('memo', "`memo`"),
                            $this->v3('created', "`created`"),
                            $this->v3('updated', "`updated`")
                        ]
                    )
                ),
            ") VALUES (",
                implode(
                    ",",
                    array_filter(
                        [
                            $this->v3('id', ":id", "NULL"),
                            $this->v3('memo', ":memo", "NULL"),
                            $this->v3('created', ":created", "NULL"),
                            $this->v3('updated', ":updated", "NULL")
                        ]
                    )
                ),
            ")"
        ];
        return implode(" ", $chunks);
    }

    /**
     * Values for query statement
     * @return array
     */
    private function values(): array
    {
        return
            array_filter(
                [
                    ':id' => $this->v3('id'),
                    ':memo' => $this->v3('memo'),
                    ':created' =>
                        $this->v3(
                            'created',
                            $this->i['created']->format("Y-m-d H:i:s")
                        ),
                    ':updated' =>
                        $this->v3(
                            'updated',
                            $this->i['updated']->format("Y-m-d H:i:s")
                        )
                ],
                function ($itm) {
                    return $itm !== null;
                }
            );
    }
}
