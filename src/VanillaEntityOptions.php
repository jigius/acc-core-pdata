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

use Acc\Core\MediaInterface;
use Acc\Core\PersistentData\EntityOptionsInterface;
use Acc\Core\PrinterInterface;
use LogicException;

/**
 * Class VanillaEntityOptions
 * @package Acc\Core\PersistentData
 */
final class VanillaEntityOptions implements EntityOptionsInterface
{
    /**
     * @var array An input data
     */
    private array $i;

    /**
     * @var array A prepared data for printing
     */
    private array $o;

    /**
     * VanillaEntityOptions constructor.
     */
    public function __construct()
    {
        $this->i = [
            'persisted' => false,
            'dirty' => false
        ];
    }

    /**
     * @inheritDoc
     * @return PrinterInterface
     */
    public function with(string $key, $val): EntityOptionsInterface
    {
        if ($this->o !== null) {
            throw new LogicException("print job is already finished");
        }
        $obj = $this->blueprinted();
        $obj->i['key'] = $val;
        return $obj;
    }

    /**
     * @inheritDoc
     * @return MediaInterface
     */
    public function finished(): MediaInterface
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
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function option(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->i)) {
            return $default;
        }
        return $this->i[$key];
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
