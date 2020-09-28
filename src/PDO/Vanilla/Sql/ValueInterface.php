<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\PersistentData\PDO;

interface ValueInterface
{
    public function processed(PDO\ValuesInterface $values): PDO\ValuesInterface;
}
