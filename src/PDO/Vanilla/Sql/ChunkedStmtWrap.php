<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

abstract class ChunkedStmtWrap implements ChunkedStmtInterface
{
    /**
     * @var ChunkedStmtInterface
     */
    private ChunkedStmtInterface $origin;

    public function __construct(ChunkedStmtInterface $chunkedStmt)
    {
        $this->origin = $chunkedStmt;
    }

    public function with(ChunkInterface $c): ChunkedStmtInterface
    {
        return $this->origin->with($c);
    }

    public function withProcessor(callable $p): ChunkedStmtInterface
    {
        return $this->origin->withProcessor($p);
    }

    public function up(): ChunkedStmtInterface
    {
        return $this->origin->up();
    }

    public function nested(): ChunkedStmtInterface
    {
        return $this->origin->nested();
    }

    public function processed(): string
    {
        return $this->origin->processed();
    }
}
