<?php

namespace Functional\Api\V1\Controllers;

use App\TestCase;

class SwaggerControllerTest extends TestCase
{
    /**
     * @group RV-15
     */
    public function testSwagger()
    {
        $this->get('api/swagger')
            ->assertStatus(200);
    }
}
