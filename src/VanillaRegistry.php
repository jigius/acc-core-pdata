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

namespace Acc\Core\PersistentData;

use Acc\Core\MediaInterface;
use Acc\Core\PrinterInterface;
use LogicException;

/**
 * Class VanillaRegistry
 * @package Acc\Core\PersistentData
 */
final class VanillaRegistry implements RegistryInterface
{
    /**
     * @var array An input data
     */
    private array $i;

    /**
     * @var array|null A prepared data for printing
     */
    private ?array $o = null;

    /**
     * VanillaEntityOptions constructor.
     */
    public function __construct()
    {
        $this->i = [];
    }

    /**
     * @inheritDoc
     * @return PrinterInterface
     */
    public function with(string $key, $val): PrinterInterface
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
     * @return RegistryInterface
     */
    public function finished(): RegistryInterface
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = $this->blueprinted();
        $obj->o = $this->i;
        $obj->i = [];
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        if ($this->o === null) {
            throw new LogicException("print job has not been finish");
        }
        foreach ($this->o as $key => $val) {
            $printer = $printer->with($key, $val);
        }
        return $printer->finished();
    }

    /**
     * @inheritDoc
     * @return mixed|null
     */
    public function value(string $key, callable $success = null, callable $failed = null)
    {
        if (!array_key_exists($key, $this->i)) {
            $ret = null;
            if ($failed !== null) {
                $ret = call_user_func($failed, $key, $this);
            }
        } else {
            $ret = $this->i[$key];
            if ($success !== null) {
                $ret = call_user_func($success, $ret, $this);
            }
        }
        return $ret;
    }

    /**
     * Clones the instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self();
        $obj->i = $this->i;
        return $obj;
    }
}
