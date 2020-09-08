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

namespace Acc\Core\PersistentData\PDO\MySql\Connection\Lazy;

use Acc\Core\PersistentData\PDO\ExtendedPDOInterface;

final class Queue implements QueueInterface
{
    private array $request;

    private array $attrs;

    public function __construct()
    {
        $this->request = [];
        $this->attrs = [];
    }

    public function withAttribute(callable $callee): self
    {
        $obj = $this->blueprinted();
        $obj->attrs[] = $callee;
        return $obj;
    }

    public function withRequest(RequestInterface $req): self
    {
        $obj = $this->blueprinted();
        if (!empty($this->request)) {
            if ($req->hashTheSame(end($this->request))) {
                array_pop($obj->request);
                return $obj;
            }
        }
        array_push($obj->request, $req);
        return $obj;
    }

    public function process(ExtendedPDOInterface $pdo): void
    {
        array_walk(
            $this->request,
            function (RequestInterface $req) use ($pdo): void {
                $req->process($pdo);
            }
        );
    }

    private function blueprinted(): self
    {
        $obj = new self();
        $obj->attrs = $this->attrs;
        $obj->request = $this->request;
        return $obj;
    }
}
