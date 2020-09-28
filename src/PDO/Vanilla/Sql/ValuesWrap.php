<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\PersistentData\PDO;

abstract class ValuesWrap implements ValuesInterface
{
    /**
     * @var ValuesInterface
     */
    private ValuesInterface $origin;

    public function __construct(ValuesInterface $values)
    {
        $this->origin = $values;
    }

    public function with(ValueInterface $v): ValuesInterface
    {
        return $this->origin->with($v);
    }

    public function processed(): PDO\ValuesInterface
    {
        return $this->origin->processed();
    }
}
