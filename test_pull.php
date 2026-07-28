<?php
require 'vendor/autoload.php';

// Mock DB
class MockCommand {
    public function __construct(public string $sql, public array $params) {}
    public function queryScalar() { return false; }
}

class MockDb implements \Yiisoft\Db\Connection\ConnectionInterface {
    public function createCommand(?string $sql = null, array $params = []): \Yiisoft\Db\Command\CommandInterface {
        // Can't easily mock return type without implementing the interface... Wait!
    }
    // ... we don't need to mock it if we just use an anonymous class that implements it? It's too big.
}
