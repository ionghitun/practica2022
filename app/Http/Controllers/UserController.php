<?php

namespace App\Http\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ForgotPasswordEmail;
use App\Notifications\VerifyEmail;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 *
 */
class UserController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $error = false;

            /** @var User $user */
            $user = User::where('email', $request->get('email'))->first();
            if (!$user) {
                $error = true;
            } else {
                if (!Hash::check($request->get('password'), $user->password)) {
                    $error = true;
                }
            }

            if ($error) {
                return $this->sendError('Bad credentials!');
            }

            if (!$user->email_verified_at) {
                return $this->sendError('User didn\'t verify email address', [], Response::HTTP_NOT_ACCEPTABLE);
            }

            $token = $user->createToken('Practica');

            return $this->sendResponse([
                'token' => $token->plainTextToken,
                'user' => $user->toArray()
            ]);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $user = new User();
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            $user->password = Hash::make($request->get('password'));
            $user->verify_token = Str::random(10);
            $user->save();

            $user->notify(new VerifyEmail($user->verify_token));

            return $this->sendResponse([
                'data' => 'Code for email verification sent!'
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'code' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $user = User::where('email', $request->get('email'))
                ->where('verify_token', $request->get('code'))
                ->first();

            if (!$user) {
                return $this->sendError('Bad code or email!');
            }

            $user->email_verified_at = now();
            $user->verify_token = null;
            $user->save();

            return $this->sendResponse([
                'data' => 'Email verified'
            ]);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerifyEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $user = User::where('email', $request->get('email'))
                ->first();

            if ($user->email_verified_at) {
                return $this->sendError('User has already email verified!', [], Response::HTTP_NOT_ACCEPTABLE);
            }

            $user->notify(new VerifyEmail($user->verify_token));

            return $this->sendResponse([
                'data' => 'Code for email verification sent!'
            ]);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $oldPasswordReset = PasswordReset::where('email', $request->get('email'))
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($oldPasswordReset) {
                if ($oldPasswordReset->created_at > Carbon::now()->subHour()) {
                    return $this->sendError('User already requested a password reset code il last hour!', [], Response::HTTP_NOT_ACCEPTABLE);
                }
            }

            $user = User::where('email', $request->get('email'))->first();

            $passwordReset = new PasswordReset();
            $passwordReset->email = $request->get('email');
            $passwordReset->token = Str::random(16);
            $passwordReset->created_at = now();
            $passwordReset->save();

            $user->notify(new ForgotPasswordEmail($passwordReset->token));

            return $this->sendResponse([
                'data' => 'Code for reset password sent on email!'
            ]);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email|exists:password_resets,email',
                'code' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $user = User::where('email', $request->get('email'))->first();
            $passwordReset = PasswordReset::where('email', $request->get('email'))
                ->where('token', $request->get('code'))
                ->first();

            if (!$passwordReset) {
                return $this->sendError('Bad code or email!');
            }

            if ($passwordReset->created_at < Carbon::now()->subHour()) {
                return $this->sendError('Time exceeded to use the code, please request a new code', [], Response::HTTP_NOT_ACCEPTABLE);
            }

            DB::beginTransaction();

            $user->password = Hash::make($request->get('password'));
            $user->save();

            $passwordReset->delete();

            DB::commit();

            return $this->sendResponse([
                'data' => 'Password changed'
            ]);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUser(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'name' => 'nullable',
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => 'nullable'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $user->name = $request->get('name', $user->name);

            if ($request->has('email') && $request->get('email') !== $user->email) {
                $user->email = $request->get('email');
                $user->email_verified_at = null;
                $user->verify_token = Str::random(10);

                $user->notify(new VerifyEmail($user->verify_token));
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->get('password'));
            }

            $user->save();

            return $this->sendResponse($user->toArray());
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
