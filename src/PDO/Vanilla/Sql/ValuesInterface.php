<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\PersistentData\PDO;

interface ValuesInterface
{
    public function with(ValueInterface $v): ValuesInterface;

    public function processed(): PDO\ValuesInterface;
}
