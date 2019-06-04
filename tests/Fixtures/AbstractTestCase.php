<?php

namespace Artgris\Bundle\PageBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTestCase extends WebTestCase
{
    /** @var Client */
    protected static $client;

    protected function setUp()
    {
        $this->initClient();
        $this->initDatabase();
    }

    protected function initClient(array $options = [])
    {
        static::$client = static::createClient($options);
    }

    protected function getBackendPage()
    {
        return static::$client->request('GET', '/admin/?entity=ArtgrisPage');
    }

    protected function initDatabase()
    {
        $buildDir = __DIR__.'/../../build';
        $originalDbPath = $buildDir.'/original_test.db';
        $targetDbPath = $buildDir.'/test.db';
        if (!\file_exists($originalDbPath)) {
            throw new \RuntimeException(\sprintf("The fixtures file used for the tests (%s) doesn't exist. This means that the execution of the bootstrap.php script that generates that file failed. Open %s/bootstrap.php and replace `NullOutput as ConsoleOutput` by `ConsoleOutput` to see the actual errors in the console.", $originalDbPath, \realpath(__DIR__.'/..')));
        }
        \copy($originalDbPath, $targetDbPath);
    }

}