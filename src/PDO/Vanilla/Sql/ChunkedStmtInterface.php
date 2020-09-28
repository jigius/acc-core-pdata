<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

interface ChunkedStmtInterface extends ChunkInterface
{
    public function with(ChunkInterface $c): ChunkedStmtInterface;

    public function withProcessor(callable $p): ChunkedStmtInterface;

    public function up(): ChunkedStmtInterface;

    public function nested(): ChunkedStmtInterface;
}
