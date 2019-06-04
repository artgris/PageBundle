<?php

namespace Artgris\Bundle\PageBundle\Tests;


use Artgris\Bundle\PageBundle\Tests\Fixtures\AbstractTestCase;

class ConfigTest extends AbstractTestCase
{
    protected static $options = ['environment' => 'test'];

    public function testDefaultConfManager()
    {
        $this->getBackendPage();
        $this->assertSame(
            200,
            static::$client->getResponse()->getStatusCode()
        );
    }

}