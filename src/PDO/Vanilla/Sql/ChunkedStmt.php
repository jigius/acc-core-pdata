<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use LogicException;

final class ChunkedStmt implements ChunkedStmtInterface
{
    private ?self $up = null;

    /**
     * @var array
     */
    private array $q;

    /**
     * @var callable|null
     */
    private $processor;

    public function __construct()
    {
        $this->q = [];
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

    public function processed(): string
    {
        $p =
            $this->processor ??
                function (array $v): string {
                    return implode(" ", $v);
                };
        return
            call_user_func(
                $p,
                array_filter(
                    array_map(
                        function (ChunkInterface $c) {
                            return $c->processed();
                        },
                        $this->q
                    )
                )
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
