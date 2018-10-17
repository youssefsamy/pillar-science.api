<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;

/**
 * @SWG\Swagger(
 *     schemes={"http","https"},
 *     host="api.v2.pillar.science/api",
 *     basePath="/",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Pillar Science API",
 *         description="Api backend on which Pillar Science research platforms runs",
 *         termsOfService="",
 *         @SWG\Contact(
 *             email="contact@mysite.com"
 *         ),
 *         @SWG\License(
 *             name="Private License",
 *             url="URL to the license"
 *         )
 *     ),
 *     @SWG\ExternalDocumentation(
 *         description="Find out more about my website",
 *         url="http..."
 *     )
 * )
 */
class SwaggerController extends Controller
{
    public function swagger()
    {
        return response()->json(\Storage::get('swagger.json'));
    }
}