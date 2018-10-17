<?php

namespace App\Functional\Services\MimeTypes;

use App\Services\MimeTypes\TabSeparatedValuesMatcher;
use App\TestCase;

class TabSeparatedValuesMatcherTest extends TestCase
{
    use MimeTypesDataProviderTrait;

    /**
     * @group RV-54
     * @dataProvider tabsPathProvider
     */
    public function testMatch($path)
    {
        $this->assertTrue(TabSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider csvPathProvider
     */
    public function testNotMatch($path)
    {
        $this->assertNull(TabSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider singleColumnProvider
     */
    public function testSingleColumn($path)
    {
        $this->assertNull(TabSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }

    /**
     * @group RV-54
     * @dataProvider textPlainProvider
     */
    public function testTextPlain($path)
    {
        $this->assertNull(TabSeparatedValuesMatcher::match(file($path), pathinfo($path)['extension'] ?? null));
    }
}

