<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;
use Acc\Core\Value\ValueInterface;

interface ChunkInterface
{
    public function processed(RegistryInterface $beans): ValueInterface;
}
