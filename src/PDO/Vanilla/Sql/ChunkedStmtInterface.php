<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;

interface ChunkedStmtInterface extends ChunkInterface
{
    public function with(ChunkInterface $c): ChunkedStmtInterface;

    public function withProcessor(callable $p): ChunkedStmtInterface;

    public function up(): ChunkedStmtInterface;

    public function nested(): ChunkedStmtInterface;
}
