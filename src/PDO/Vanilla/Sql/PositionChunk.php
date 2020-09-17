<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Sql;

use Acc\Core\Registry\BeansInterface;

final class PositionChunk implements ChunkInterface
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
     * @var BeansInterface
     */
    private BeansInterface $p;

    /**
     * @var string
     */
    private string $n;

    public function __construct(BeansInterface $p, string $name, callable $success = null, callable $failed = null)
    {
        $this->p = $p;
        $this->n = $name;
        $this->s = $success;
        $this->f = $failed;
    }

    public function processed(): string
    {
        if ($this->p->defined($this->n)) {
            $r = $this->s? call_user_func($this->s, $this->p->fetch($this->n)): $this->s;
        } else {
            $r = $this->f? call_user_func($this->f, $this->n): "";
        }
        return $r;
    }
}
