<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;
use Acc\Core\Value\ValueInterface;

final class CalculatedChunk implements ChunkInterface
{
    /**
     * @var callable
     */
    private $p;

    public function __construct(callable $processor)
    {
        $this->p = $processor;
    }

    public function processed(RegistryInterface $beans): ValueInterface
    {
        return call_user_func($this->p, $beans);
    }
}
