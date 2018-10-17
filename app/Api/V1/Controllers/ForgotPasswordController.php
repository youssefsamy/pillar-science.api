<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ForgotPasswordRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ForgotPasswordController
 *
 * @package App\Api\V1\Controllers
 *
 * @author Mathieu Tanguay <mathieu@pillar.science>
 * @copyright Pillar Science
 *
 * @resource Authentication
 */
class ForgotPasswordController extends Controller
{
    /**
     * Send Reset Email
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetEmail(ForgotPasswordRequest $request)
    {
        $broker = $this->getPasswordBroker();
        $sendingResponse = $broker->sendResetLink($request->only('email'));

        if($sendingResponse !== Password::RESET_LINK_SENT) {
            throw new NotFoundHttpException();
        }

        return response()->json([
            'status' => 'ok',
        ], 200);
    }

    /**
     * Get Password Broker
     *
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    private function getPasswordBroker()
    {
        return Password::broker();
    }
}
