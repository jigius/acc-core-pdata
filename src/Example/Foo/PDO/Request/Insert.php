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
use Acc\Core\PersistentData\PDO\{
    PDOStatementInterface,
    ExtendedPDOInterface,
    Value,
    Values,
    ValuesInterface
};
use Acc\Core\PersistentData\RequestInterface;
use Acc\Core\PrinterInterface;
use DomainException, LogicException, DateTimeImmutable;

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
     * @var array An input data
     */
    private array $i;

    /**
     * @var array|null A prepared data for printing
     */
    private ?array $o = null;

    /**
     * @var PDOStatementInterface|null
     */
    private ?PDOStatementInterface $statement = null;

    /**
     * Insert constructor.
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
        $obj = $this->blueprinted();
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
        $obj = $this->blueprinted();
        $obj->o = $obj->i;
        $obj->i = [];
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
                ->with('statement', $this->statement)
                ->finished();
    }

    /**
     * @inheritDoc
     * @param ExtendedPDOInterface $pdo
     * @return RequestInterface
     * @throws DomainException
     */
    public function executed(ExtendedPDOInterface $pdo): RequestInterface
    {
        if ($this->o === null) {
            return $this->entity->printed($this)->executed($pdo);
        }
        $query = $this->sqlStatement();
        if (empty($query)) {
            return $this;
        }
        $this->validate();
        $obj = $this->blueprinted();
        $obj
            ->statement =
                $pdo
                    ->prepared(
                        $this->sqlStatement()
                    )
                    ->withValues(
                        $this->values()
                    )
                    ->executed();
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
                    return $carry && isset($this->o[$key]);
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
     * @param mixed $val
     * @param callable $processor
     * @return mixed
     */
    private function processed($val, callable $processor)
    {
        return call_user_func($processor, $val);
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
     * Query's values
     * @return ValuesInterface
     */
    private function values(): ValuesInterface
    {
        $arr =
            array_filter(
                [
                    ':id' => $this->v3('id'),
                    ':memo' => $this->v3('memo'),
                    ':created' =>
                        $this->processed(
                            $this->v3('created'),
                            function (?DateTimeImmutable $dt = null) {
                                if ($dt === null) {
                                    return null;
                                }
                                return $dt->format("Y-m-d H:i:s");
                            }
                        ),
                    ':updated' =>
                        $this->processed(
                            $this->v3('updated'),
                            function (?DateTimeImmutable $dt = null) {
                                if ($dt === null) {
                                    return null;
                                }
                                return $dt->format("Y-m-d H:i:s");
                            }
                        ),
                ],
                function ($itm) {
                    return $itm !== null;
                }
            );
        $vals = new Values();
        $bp = new Value();
        foreach ($arr as $key => $val) {
            $vals =
                $vals
                    ->with(
                        $bp
                            ->withName($key)
                            ->withValue($val)
                    );
        }
        return $vals;
    }

    /**
     * Clones the instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->entity);
        $obj->i = $this->i;
        $obj->o = $this->o;
        $obj->statement = $this->statement;
        return $obj;
    }
}
