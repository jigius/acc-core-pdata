<?php
namespace Acc\Core\PersistentData\Example\Foo\PDO;

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
     * @var array
     */
    private array $o;

    /**
     * PreparedEntity constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->orig = $entity;
    }

    /**
     * @inheritDoc
     * @param PrinterInterface $p
     * @return $this
     * @throws LogicException
     */
    public function printed(PrinterInterface $printer): self
    {
        if (empty($this->i)) {
            throw new LogicException("print job has not been run");
        }
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
     * @param int $id
     * @return EntityInterface
     */
    public function withId(int $id): EntityInterface
    {
        return new self($this->orig->withId($id));
    }

    /**
     * @param string $memo
     * @return EntityInterface
     */
    public function withMemo(string $memo): EntityInterface
    {
        return new self($this->orig->withMemo($memo));
    }

    /**
     * @param DateTimeImmutable $dt
     * @return EntityInterface
     */
    public function withCreated(DateTimeImmutable $dt): EntityInterface
    {
        return new self($this->orig->withCreated($dt));
    }

    /**
     * @param DateTimeImmutable|null $dt
     * @return EntityInterface
     */
    public function withUpdated(DateTimeImmutable $dt = null): EntityInterface
    {
        return new self($this->orig->withUpdated($dt));
    }

    /**
     * @param string $key
     * @param mixed $val
     * @return EntityInterface
     */
    public function withOption(string $key, $val): EntityInterface
    {
        return new self($this->orig->withOption($key, $val));
    }

    /**
     * @inheritDoc
     * @return string
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
     * @return EntityInterface
     */
    public function finished(): EntityInterface
    {
        if (empty($this->i)) {
            throw new LogicException("print job has not been run");
        }
        $obj = $this->blueprinted();
        $obj->o = $this->i;
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
        return $obj;
    }

    /**
     * Validates input data
     */
    private function validate(): void
    {
        if (!empty($this->i['updated']) && $this->i['updated'] < $this->i['created']) {
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
            if (empty($this->i[$name])) {
                continue;
            }
            if (mb_strlen($this->i[$name]) > $len) {
                $obj->i[$name] = mb_substr($this->i[$name], 0, $len);
            }
        }
        return $obj;
    }
}
