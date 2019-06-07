<?php

namespace Artgris\Bundle\PageBundle\Tests;

use Artgris\Bundle\PageBundle\Tests\Fixtures\AbstractTestCase;

class ConfigTest extends AbstractTestCase
{
    protected static $options = ['environment' => 'test'];

    public function testListManager()
    {
        $this->getArtgrisPageListView();
        $this->assertSame(
            200,
            static::$client->getResponse()->getStatusCode()
        );
    }

    public function testShowManager()
    {
        $this->requestShowView();
        $this->assertSame(
            403,
            static::$client->getResponse()->getStatusCode()
        );
    }

    public function testEditManager()
    {
        $this->requestEditView();
        $this->assertSame(
            200,
            static::$client->getResponse()->getStatusCode()
        );
    }

    public function testEditConfigManager()
    {
        $this->requestEditConfigurationView();
        $this->assertSame(
            200,
            static::$client->getResponse()->getStatusCode()
        );
    }
}
