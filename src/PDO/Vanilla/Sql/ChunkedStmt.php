<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;
use Acc\Core\Value\ValueInterface;
use Acc\Core\Value\Vanilla\Value;
use LogicException;

final class ChunkedStmt implements ChunkedStmtInterface
{
    /**
     * @var self|null
     */
    private ?self $up = null;
    /**
     * @var array
     */
    private array $q;
    /**
     * @var callable|null
     */
    private $processor;
    /**
     * @var ValueInterface|null
     */
    private ?ValueInterface $value;

    public function __construct(?ValueInterface $value = null)
    {
        $this->q = [];
        $this->value = $value;
        $this->processor = null;
    }

    public function with(ChunkInterface $c): self
    {
        $obj = $this->blueprinted();
        $obj->q[] = $c;
        return $obj;
    }

    public function withProcessor(callable $p): self
    {
        $obj = $this->blueprinted();
        $obj->processor = $p;
        return $obj;
    }

    public function up(): self
    {
        if ($this->up === null) {
            throw new LogicException("there is no up level");
        }
        $obj = $this->up->blueprinted();
        $obj->q[] = $this;
        return $obj;
    }

    public function nested(): self
    {
        $obj = new self();
        $obj->up = $this;
        return $obj;
    }

    public function processed(RegistryInterface $beans): ValueInterface
    {
        $ar =
            array_map(
                function (ValueInterface $v) {
                    return $v->fetch();
                },
                array_filter(
                    array_map(
                        function (ChunkInterface $c) use ($beans) {
                            return $c->processed($beans);
                        },
                        $this->q
                    ),
                    function (ValueInterface $v) {
                        return $v->defined();
                    }
                )
            );
        return
            ($this->value ?? new Value())
                ->assign(
                    $this->processor === null? $ar: call_user_func($this->processor, $ar)
                );
    }

    private function blueprinted(): self
    {
        $obj = new self();
        $obj->up = $this->up;
        $obj->processor = $this->processor;
        $obj->q = $this->q;
        return $obj;
    }
}
