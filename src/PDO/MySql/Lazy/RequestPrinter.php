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

namespace Acc\Core\PersistentData\PDO\MySql\Lazy;

use Acc\Core\PrinterInterface;
use DomainException;

final class RequestPrinter implements RequestPrinterInterface
{
    private array $i;

    public function __construct()
    {
        $this->i = [];
    }

    public function with(string $key, $val): PrinterInterface
    {
        $obj = $this->blueprinted();
        $obj->i[$key] = $val;
        return $obj;
    }

    public function finished(): RequestInterface
    {
        if (empty($this->i['hash']) || !is_string($this->i['hash'])) {
            throw new DomainException("input data with name=`hash` is invalid");
        }
        if (empty($this->i['cb']) || !is_callable($this->i['hash'])) {
            throw new DomainException("input data with name=`cb` is invalid");
        }
        return new Request(md5($this->i['hash']), $this->i['cb']);
    }

    private function blueprinted(): self
    {
        $obj = new self();
        $obj->i = $this->i;
        return $obj;
    }
}
