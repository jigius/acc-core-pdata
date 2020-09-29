<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;
use Acc\Core\Value\ValueInterface;
use Acc\Core\Value\Vanilla\Value;

final class StaticChunk implements ChunkInterface
{
    /**
     * @var mixed
     */
    private $val;

    /**
     * @var ValueInterface|null
     */
    private ?ValueInterface $bp;

    public function __construct($value, ValueInterface $bp = null)
    {
        $this->val = $value;
        $this->bp = $bp;
    }

    public function processed(RegistryInterface $beans): ValueInterface
    {
        $val = $this->val;
        if (!($val instanceof ValueInterface)) {
            $val =
                ($this->bp ?? new Value())
                    ->assign($val);
        }
        return $val;
    }
}
