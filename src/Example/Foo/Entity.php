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

namespace Acc\Core\PersistentData\Example\Foo;

use Acc\Core\PersistentData\EntityOptionsInterface;
use Acc\Core\PersistentData\VanillaEntityOptions;
use Acc\Core\PrinterInterface;
use DateTimeImmutable, LogicException, Exception;

final class Entity implements EntityInterface
{
    /**
     * @var array An input data
     */
    private array $i;

    /**
     * @var string|null
     */
    private ?string $identity = null;

    /**
     * @var EntityOptionsInterface
     */
    private EntityOptionsInterface $opts;

    /**
     * @inheritDoc
     * Entity constructor.
     * @param PrinterInterface|null $opts
     */
    public function __construct(EntityOptionsInterface $opts = null)
    {
        $this->i = [];
        $this->opts = $opts ?? new VanillaEntityOptions();
    }

    /**
     * @inheritDoc
     * @param PrinterInterface $printer
     * @return mixed
     */
    public function printed(PrinterInterface $printer)
    {
        foreach ($this->i as $key => $val) {
            $printer = $printer->with($key, $val);
        }
        return
            $printer
                ->with('opts', $this->opts)
                ->finished();
    }

    /**
     * @inheritDoc
     * @param string $id
     * @return EntityInterface
     * @throws Exception
     */
    public function withId(string $id): EntityInterface
    {
        if (!empty($this->i['id']) && $this->i['id'] !== $id && $this->opts['persisted']) {
            throw new LogicException("the changing value of pk is prohibited");
        }
        return $this->with('id', $id);
    }

    /**
     * @inheritDoc
     * @param string $memo
     * @return EntityInterface
     * @throws Exception
     */
    public function withMemo(string $memo): EntityInterface
    {
        return $this->with('memo', $memo);
    }

    /**
     * @inheritDoc
     * @param DateTimeImmutable $dt
     * @return EntityInterface
     * @throws Exception
     */
    public function withCreated(DateTimeImmutable $dt): EntityInterface
    {
        return $this->with('created', $dt);
    }

    /**
     * @inheritDoc
     * @param DateTimeImmutable|null $dt
     * @return EntityInterface
     * @throws Exception
     */
    public function withUpdated(DateTimeImmutable $dt = null): EntityInterface
    {
        return $this->with('updated', $dt);
    }

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return EntityInterface
     * @throws Exception
     */
    public function withOption(string $key, $val): EntityInterface
    {
        $obj = $this->blueprinted();
        $obj->opts = $this->opts->with($key, $val);
        return $obj;
    }

    /**
     * @inheritDoc
     * @return EntityOptionsInterface
     */
    public function options(): EntityOptionsInterface
    {
        return $this->opts;
    }

    /**
     * @inheritDoc
     */
    public function identity(): string
    {
        if ($this->identity === null) {
            throw new LogicException("the state is invalid");
        }
        return $this->identity;
    }

    /**
     * @return EntityInterface
     * @throws Exception
     */
    private function blueprinted(): EntityInterface
    {
        $obj = new self($this->opts);
        $obj->identity = $this->identity ?? bin2hex(random_bytes(8)); /* For example purpose only! */
        $obj->i = $this->i;
        return $obj;
    }

    /**
     * @param string $k A name of the part of a data
     * @param $v int|string|float|bool The value of the part of a data
     * @return EntityInterface
     * @throws Exception
     */
    private function with(string $k, $v): EntityInterface
    {
        $changed = isset($this->i[$k]) && $this->i[$k] !== $v || $v !== null || !in_array($k, $this->i);
        if (!$changed) {
            return $this;
        }
        $obj = $this->blueprinted();
        $obj->opts = $this->opts->with('dirty', true);
        $obj->i[$k] = $v;
        return $obj;
    }
}
