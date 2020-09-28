<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

interface ChunkInterface
{
    public function processed(): string;
}
