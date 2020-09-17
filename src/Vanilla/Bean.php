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

namespace Acc\Core\PersistentData\Vanilla;

use Acc\Core\PersistentData\BeanInterface;
use Acc\Core\PrinterInterface;
use Acc\Core\Registry;
use LogicException;

/**
 * Class Bean
 * Implements public contract
 * @package Acc\Core\PersistentData\Vanilla
 */
final class Bean implements BeanInterface
{
    /**
     * @var Registry\BeansInterface|null
     */
    protected ?Registry\BeansInterface $p;

    /**
     * @var Registry\BeansInterface|null
     */
    protected ?Registry\BeansInterface $a;

    public function __construct(?Registry\BeansInterface $property = null, ?Registry\BeansInterface $attribute = null)
    {
        $this->p = $property;
        $this->a = $attribute;
    }

    /**
     * @inheritDoc
     */
    public function withProp(string $name, $val): self
    {
        $obj = $this->blueprinted();
        $pod = $this->p ?? new Registry\Vanilla\Pod();
        if ($pod->defined($name)) {
            $pea = $pod->pulled($name);
        } else {
            $pea = new Registry\Vanilla\Pea();
        }
        $obj->p =
            $pod
                ->pushed(
                    $name,
                    $pea->withValue($val)
                );
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function withAttr(string $name, $val): self
    {
        $obj = $this->blueprinted();
        $obj->p =
            ($this->a ?? new Registry\Vanilla\Pod())
                ->pushed($name, $val);
        return $obj;
    }

    public function attrs(): Registry\BeansInterface
    {
       return $this->a ?? new Registry\Vanilla\Pod();
    }

    public function printed(PrinterInterface $printer)
    {
        if ($this->p !== null) {
            foreach ($this->p->iterator() as $key => $pea) {
                if (!($pea instanceof Registry\BeanInterface)) {
                    throw new LogicException("pea with key=`{$key}` has invalid type");
                }
                $val = $pea->value();
                if (!$val->defined()) {
                    continue;
                }
                if (!($val instanceof Registry\Vanilla\DefinedValue)) {
                    throw new LogicException("a value for pea with key=`{$key}` has invalid type");
                }
                $printer = $printer->with($key, $val->fetch());
            }
        }
        return $printer->finished();
    }

    /**
     * Clones the instance
     * @return self
     */
    private function blueprinted(): self
    {
        return new self($this->p, $this->a);
    }
}
