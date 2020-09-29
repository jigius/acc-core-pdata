<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

use Acc\Core\Registry\RegistryInterface;
use Acc\Core\PersistentData\PDO;
use LogicException;

final class PositionValue implements ValueInterface
{
    /**
     * @var callable
     */
    private $f;

    /**
     * @var callable
     */
    private $s;

    /**
     * @var RegistryInterface
     */
    private RegistryInterface $p;

    /**
     * @var string
     */
    private string $n;

    public function __construct(RegistryInterface $p, string $name, callable $success, callable $failed = null)
    {
        $this->p = $p;
        $this->n = $name;
        $this->s = $success;
        $this->f = $failed;
    }

    public function processed(PDO\ValuesInterface $values): PDO\ValuesInterface
    {
        $val = null;
        if ($this->p->defined($this->n)) {
            $val = call_user_func($this->s, $this->p->pulled($this->n));
        } else {
            if ($this->f !== null) {
                $val = call_user_func($this->f, $this->n);
            }
        }
        if ($val !== null) {
            if (!($val instanceof PDO\ValueInterface)) {
                throw new LogicException("invalid type");
            }
            $values = $values->with($val);
        }
        return $values;
    }
}
