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
 * Class Update
 * @package Acc\Core\PersistentData\Example\Foo\PDO\Request
 */
final class Update implements RequestInterface, PrinterInterface
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
     * Update constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->i = [];
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
        $opts = $this->entity->options();
        if ($opts->option('persisted') === null || $opts->option('dirty') === null) {
            throw new DomainException("invalid data");
        }
        if (!$opts->option('persisted')) {
            throw new DomainException("the operation for this entity is prohibited");
        }
        $res = array_reduce(
            [
                'id', 'memo', 'created', 'updated'
            ],
            function ($carry, $key) {
                if (!empty($this->i[$key])) {
                    if ($key == "id") {
                        $carry |= 0x1;
                    } else {
                        $carry |= 0x2;
                    }
                }
                return $carry;
            },
            0
        );
        if ($res !== 0x3) {
            throw new DomainException(
                "it's detected the absence of mandatory params"
            );
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
        if (!array_key_exists($key, $this->i)) {
            return $unknown;
        }
        return
            $this->i[$key] !== null?
                (
                    $defined === null? $this->i[$key]: $defined
                ):
                (
                    $undefined === null? $defined: $undefined
                );
    }

    /**
     * @return string
     */
    private function sqlStatement(): string
    {
        $stmt = "";
        $opts = $this->entity->options();
        if ($opts->option('dirty') || $opts->option('forced', false)) {
            $chunks = [
                "UPDATE `foo`",
                "SET",
                implode(
                    ", ",
                    array_filter(
                        [
                            $this->v3('memo', "`memo`=:memo", "`memo`=NULL"),
                            $this->v3('created', "`created`=:created", "`created`=NULL"),
                            $this->v3('updated', "`updated`=:updated", "`updated`=NULL", "`updated`=NOW()")
                        ]
                    )
                ),
                "WHERE",
                "`id`=:id"
            ];
            $stmt = implode(" ", $chunks);
        }
        return $stmt;
    }

    /**
     *
     * @return array
     */
    private function values(): array
    {
        $arr = [];
        $opts = $this->entity->options();
        if ($opts->option('dirty') || $opts->option('forced', false)) {
            $arr =
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
        return $arr;
    }
}
