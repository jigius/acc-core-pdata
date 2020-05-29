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

namespace Acc\Core\PersistentData\Example\Foo\PDO;

use Acc\Core\PersistentData\EntityOptionsInterface;
use Acc\Core\PersistentData\Example\Foo\EntityInterface;
use Acc\Core\PrinterInterface;
use DateTimeImmutable;
use DomainException, LogicException;

/**
 * Class PreparedEntity
 * @package Acc\Core\PersistentData\Example\Foo\PDO
 */
final class PreparedEntity implements EntityInterface, PrinterInterface
{
    /**
     * @var EntityInterface
     */
    private EntityInterface $orig;

    /**
     * @var array
     */
    private array $i;

    /**
     * @var array|null
     */
    private ?array $o = null;

    /**
     * PreparedEntity constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->orig = $entity;
        $this->i = [];
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer): PrinterInterface
    {
        if ($this->o === null) {
            return $this->orig->printed($this)->printed($printer);
        }
        $this->validate();
        $obj = $this->sanitized();
        foreach ($obj->o as $key => $val) {
            $printer = $printer->with($key, $val);
        }
        return $printer->finished();
    }

    /**
     * @inheritDoc
     */
    public function withId(string $id): EntityInterface
    {
        return new self($this->orig->withId($id));
    }

    /**
     * @inheritDoc
     */
    public function withMemo(string $memo): EntityInterface
    {
        return new self($this->orig->withMemo($memo));
    }

    /**
     * @inheritDoc
     */
    public function withCreated(DateTimeImmutable $dt): EntityInterface
    {
        return new self($this->orig->withCreated($dt));
    }

    /**
     * @inheritDoc
     */
    public function withUpdated(DateTimeImmutable $dt = null): EntityInterface
    {
        return new self($this->orig->withUpdated($dt));
    }

    /**
     * @inheritDoc
     */
    public function withOption(string $key, $val): EntityInterface
    {
        return new self($this->orig->withOption($key, $val));
    }

    /**
     * @inheritDoc
     */
    public function options(): EntityOptionsInterface
    {
        return $this->orig->options();
    }

    /**
     * @inheritDoc
     */
    public function identity(): string
    {
        return $this->orig->identity();
    }

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return PrinterInterface
     */
    public function with(string $key, $val): PrinterInterface
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
     */
    public function finished(): EntityInterface
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
     * Clones an instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->orig);
        $obj->i = $this->i;
        $obj->o = $this->o;
        return $obj;
    }

    /**
     * Validates input data
     */
    private function validate(): void
    {
        if (!empty($this->o['updated']) && $this->o['updated'] < $this->o['created']) {
            throw new DomainException("the value of field `update` is invalid");
        }
    }

    /**
     * Clones an instance and returns it with sanitized state
     * @return EntityInterface
     */
    private function sanitized(): EntityInterface
    {
        $obj = $this->blueprinted();
        $fs = [
            'memo' => 255
        ];
        foreach ($fs as $name => $len) {
            if (empty($this->o[$name])) {
                continue;
            }
            if (mb_strlen($this->o[$name]) > $len) {
                $obj->o[$name] = mb_substr($this->o[$name], 0, $len);
            }
        }
        return $obj;
    }
}
