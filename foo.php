<?php
namespace Local\Main;

use Acc\Core\PersistentData\Entities;
use Acc\Core\PersistentData\Example\Foo;
use Acc\Core\PersistentData\PDO\FetchMode\Vanilla;
use Acc\Core\PersistentData\PDO\MySqlPDO as ExPDO, PDO;
use Acc\Core\PersistentData\PDO\Value;
use Acc\Core\PersistentData\PDO\VanillaRepository;
use RuntimeException;

require_once __DIR__ . "/vendor/autoload.php";

//var_dump(new \DateTimeImmutable("now", new \DateTimeZone("UTC")));
//exit;
$pdo = new ExPDO(
    "mysql:host=localhost;port=3306;dbname=foo;charset=utf8;timezone=UTC",
    "root",
    "GznfxjR",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_AUTOCOMMIT => 0,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
    ],
    [
        "SET SESSION time_zone='UTC'",
        "SET SESSION wait_timeout=600"
    ]
);
$r =
    $pdo->trx(
        function () use ($pdo) {
            $r =
                new Entities(
                    (new VanillaRepository($pdo))
                        ->executed(
                            new Foo\PDO\Criteria\Entity(
                                (new Foo\Entity())
                                    ->withMemo("qwerty2")
                            )
                        )
                    ->pulled(),
                    new Foo\EntityFactory()
                );
            $enty = $r->rewinded()->current();
            $enty = $enty->withMemo("asdfgh");
            (new VanillaRepository($pdo))
                ->executed(
                    new Foo\PDO\Request\Update(
                        new Foo\PDO\PreparedEntity(
                            $enty
                        )
                    )
                );
                //throw new RuntimeException("Foo");
        }
    );
/*$enty = $r->rewinded()->current();
$enty = $enty->withMemo("foobar3foobar3foobar3");
var_dump($enty);*/
/*
$stmt =
    $pdo
        ->prepared("INSERT INTO `foo` (memo, created) VALUES (:memo, :created)")
        ->withValue(
            (new Value())
                ->withName(":memo")
                ->withValue("blabla")
        )
        ->withValue(
            (new Value())
                ->withName(":created")
                ->withValue('2020-05-01 12:00:00')
        )
        ->withFetchMode(
            new Vanilla(PDO::FETCH_BOTH)
        )
        ->executed();
var_dump($stmt->orig()->rowCount());
*/
