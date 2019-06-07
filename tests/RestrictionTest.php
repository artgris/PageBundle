<?php

namespace Artgris\Bundle\PageBundle\Tests;

use Artgris\Bundle\PageBundle\Tests\Fixtures\AbstractTestCase;

class RestrictionTest extends AbstractTestCase
{
    protected static $options = ['environment' => 'redirection'];

    public function testEditManager()
    {
        $this->requestEditView();
        $this->assertSame(
            200,
            static::$client->getResponse()->getStatusCode()
        );
    }
}
