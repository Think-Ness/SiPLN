<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;

require dirname(__DIR__) . '/vendor/autoload.php';

$configPaths = new ConfigPaths(dirname(__DIR__), 'config');
$config = new Config($configPaths, null, [
    RecursiveMerge::groups('events', 'events-web', 'events-console'),
]);

$containerConfig = ContainerConfig::create()
    ->withDefinitions($config->get('common'))
    ->withProviders($config->get('providers-common'));

$container = new Container($containerConfig);

$db = $container->get(ConnectionInterface::class);

$db->createCommand("ALTER TABLE surat_mailing ADD COLUMN sort_by VARCHAR(50) DEFAULT 'nama ASC' AFTER catatan;")->execute();
echo "Table altered successfully!";
