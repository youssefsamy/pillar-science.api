<?php

namespace App\Functional\Services\MimeTypes;

use App\Services\MimeTypes\CommaSeparatedValuesMatcher;
use App\TestCase;

class CommaSeparatedValuesMatcherTest extends TestCase
{
    use MimeTypesDataProviderTrait;

    /**
     * @group RV-54
     * @dataProvider csvPathProvider
     */
    public function testMatch($path)
    {
        $this->assertTrue(CommaSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider tabsPathProvider
     */
    public function testNotMatch($path)
    {
        $this->assertNull(CommaSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider singleColumnProvider
     */
    public function testSingleColumn($path)
    {
        $this->assertNull(CommaSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider textPlainProvider
     */
    public function testTextPlain($path)
    {
        $this->assertNull(CommaSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }
}
