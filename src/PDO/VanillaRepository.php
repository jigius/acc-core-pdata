<?php
/**
 * This file is part of the jigius/acc-core-pdata library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2020 Jigius <jigius@gmail.com>
 * @link https://github.com/jigius/acc-core-pdata GitHub
 */

declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO;

use Acc\Core\PersistentData\{RepositoryInterface, RequestInterface};
use Acc\Core\PrinterInterface;
use Iterator, IteratorIterator, LogicException;

final class VanillaRepository implements RepositoryInterface, PrinterInterface
{
    /**
     * @var ExtendedPDOInterface
     */
    private ExtendedPDOInterface $pdo;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $r = null;

    /**
     * @var array
     */
    private array $i;

    /**
     * @var array|null
     */
    private ?array $o = null;

    /**
     * Repository constructor.
     * @param ExtendedPDOInterface $pdo
     */
    public function __construct(ExtendedPDOInterface $pdo)
    {
        $this->pdo = $pdo;
        $this->i = [];
    }

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return $this
     */
    public function with(string $key, $val): self
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = $this->blueprinted();
        $obj->i[$key] = $val;
        return $obj;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function finished(): self
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = $this->blueprinted();
        $obj->o = $obj->i;
        $obj->i = [];
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function pulled(): Iterator
    {
        if ($this->r === null) {
            throw new LogicException("has not being requested yet");
        }
        if ($this->o === null) {
            return $this->r->printed($this)->pulled();
        }
        if (!isset($this->o['statement'])) {
            throw new LogicException("invalid data");
        }
        return new IteratorIterator($this->o['statement']->orig());
    }

    /**
     * @inheritDoc
     */
    public function executed(RequestInterface $request): RepositoryInterface
    {
        $obj = $this->blueprinted();
        $obj->r = $request->executed($this->pdo);
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        return
            $printer
                ->with('request', $this->r)
                ->finished();
    }

    /**
     * Clones the instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->pdo);
        $obj->i = $this->i;
        $obj->o = $this->o;
        $obj->r = $this->r;
        return $obj;
    }
}
