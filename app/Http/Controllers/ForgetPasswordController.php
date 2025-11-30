<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;

use App\Exceptions\InvalidCodeException;
use App\Http\Requests\EmailAndCodeRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\EmailRequest;

use Hash;

use App\Repositories\UserRepository;

use App\Traits\ApiResponse;


use App\Services\CasheService;
use App\Services\GenerateCode;


class ForgetPasswordController extends Controller
{

    public function __construct(
        protected GenerateCode $codeService,
        protected UserRepository $userRepository,
        protected CasheService $casheService)
        {}

    public function forgotPassword(EmailRequest $request)
    {
            $user = $this->userRepository->findByEmail($request->email);

            $code = $this->codeService->generateCode($user);

            event(new UserRegistered($user, $code)) ;

        return $this->success('تم إرسال كود إلى بريدك',$code);
    }

    public function checkCode(EmailAndCodeRequest $request)
    {

        $user = $this->userRepository->findByEmail($request->email);

        $storedCode = $this->casheService->getCodeFromCashe($user);

        if (!$storedCode || $storedCode != $request->code) {
           throw new InvalidCodeException();
        }

        $this->casheService->forgetCodeFromCashe($user);

        $this->userRepository->deleteUserToken($user);

        $token = $this->userRepository->createToken($user);

        return $this->success( 'تم التحقق من الكود بنجاح', ['token' => $token]);
    }

    public function resetPassword(PasswordRequest $request)
    {
        $user = auth()->user();

        $this->userRepository->update($user,['password' => Hash::make($request->password)]);

       return $this->success('تم تغيير كلمة السر بنجاح');
    }
}
