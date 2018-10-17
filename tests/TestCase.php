<?php

namespace App;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

abstract class TestCase extends BaseTestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $token = null;

    const EMAIL = 'user@email.com';
    const PASSWORD = 'secret';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function login($email = null, $password = null)
    {
        $response = $this->post('api/auth/login', [
            'email' => $email ?? static::EMAIL,
            'password' => $password ?? static::PASSWORD
        ]);

        $response->assertStatus(200);

        $responseJSON = json_decode($response->getContent(), true);

        $this->token = $responseJSON['token'];

        return $responseJSON['token'];
    }

    public function getUser()
    {
        $response = $this->get('api/auth/me');

        $response->assertStatus(200);

        return json_decode($response->getContent(), true);
    }

    /**
     * Returns an array only bearer token header defined. May supply
     * additional headers to the headers param to merge them in.
     *
     * @param array $headers Headers to merge in
     */
    public function getHeaders($headers = [])
    {
        if (!$this->token) {
            return $headers;
        }

        return array_merge([
            'Authorization' => 'Bearer ' . $this->token
        ], $headers);
    }

    public function loginAs(User $user)
    {
        return auth()->login($user);
    }

    /**
     * This is a copy/paste from \PHPUnit\Framework\TestCase::createPartialMock
     * without the disableOriginalConstructor method call
     *
     * @param $originalClassName
     * @param array $methods
     * @return MockObject
     */
    protected function createPartialMockWithConstructor($originalClassName, array $methods): MockObject
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(empty($methods) ? null : $methods)
            ->getMock();
    }

    protected function nameDataProviderTests(array $tests)
    {
        return collect($tests)->mapWithKeys(function ($values) {
            $captionParts = array_map(function ($value) {
                if ($value === true) {
                    return 'true';
                } elseif ($value === false) {
                    return 'false';
                } elseif (is_array($value)) {
                    return '[' . implode(', ', $value) . ']';
                }

                return $value;
            }, $values);

            return ['Test case: ' . implode('-', $captionParts) => $values];
        })->toArray();
    }
}
