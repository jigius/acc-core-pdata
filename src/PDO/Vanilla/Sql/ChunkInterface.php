<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Sql;

interface ChunkInterface
{
    public function processed(): string;
}
