<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Sql;

use Acc\Core\PersistentData\PDO;
use Acc\Core\Registry\Asset;
use DomainException;

final class Values implements ValuesInterface
{
    /**
     * @var array
     */
    private array $q;

    /**
     * @var PDO\ValuesInterface
     */
    private $v;

    public function __construct(?PDO\ValuesInterface $v = null)
    {
        $this->v = $v ?? new PDO\Values();
        $this->q = [];
    }

    public function with(ValueInterface $v): self
    {
        $obj = $this->blueprinted();
        $obj->q[] = $v;
        return $obj;
    }

    public function processed(): PDO\ValuesInterface
    {
        try {
            $vs = $this->v;
            array_walk(
                $this->q,
                function (ValueInterface $v) use (&$vs): void {
                    $vs = $v->processed($vs);
                }
            );
        } catch (Asset\FailureException $ex) {
            throw new DomainException("invalid value", 0, $ex);
        }
        return $vs;
    }

    private function blueprinted(): self
    {
        $obj = new self();
        $obj->q = $this->q;
        return $obj;
    }
}
