<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Exceptions\CodeSendingException;
use App\Exceptions\InvalidCodeException;
use App\Http\Requests\EmailAndCodeRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\LoginRequest;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\ComplaintEmployeeRepository;
use App\Repositories\UserRepository;
use App\Services\CasheService;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserInformationRequest;
use App\Services\AuthService;
use App\Services\GenerateCode;
use App\Traits\ApiResponse;
use App\Http\Requests\AuthEmployee\SignInRequest as loginEmployeeRequest;
use App\Http\Requests\AuthEmployee\SignUpRequest as registerEmployeeRequest;
use App\Http\Requests\AuthAdmin\SignUpRequest as registerAdminRequest;
use App\Http\Requests\AuthAdmin\SignInRequest as loginAdminRequest;


class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService,
        protected GenerateCode $codeService,
        protected UserRepository $userRepository,
        protected CasheService $casheService,
        protected ComplaintEmployeeRepository $complaintEmployeeRepository,
        protected AdminRepository $adminRepository,
    ) {}

    /**
     * Register new user and send verification code
     *
     * @param RegisterRequest $registerRequest
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function RegisterUser(RegisterRequest $registerRequest)
    {
        $user = $this->authService->registerUser($registerRequest);

        $code = $this->codeService->generateCode($user);

        event(new UserRegistered($user, $code));

        return $this->success('تم إرسال كود التحقق', ['user' => $user, 'code' => $code]);
    }

     public function registerEmployee(registerEmployeeRequest $registerEmployeeRequest)
    {
        DB::transaction(function () use ($registerEmployeeRequest) {
        $employee = $this->complaintEmployeeRepository->createEmployee($registerEmployeeRequest->validated());
        $employee->assignRole('employee');
        return $this->success('Employee Registered Successfully', [
            'token' => $employee->createToken('employee_token')->plainTextToken,
            'employee' => $employee
            ]);
        });
    }

    public function loginEmployee(loginEmployeeRequest $loginEmployeeRequest)
    {
        $employee = $this->complaintEmployeeRepository->findByEmail($loginEmployeeRequest->email);

        if($employee &&  password_verify($loginEmployeeRequest->password, $employee->password))
        {
            return $this->success('Login Susseccfully', [
                'token' => $employee->createToken('employee_token')->plainTextToken,
                'employee' => $employee
            ], 200);
        }
        return $this->error('Informations are not Correct',null, 401);
    }

    public function logoutEmployee(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success("تم تسجيل الخروج بنجاح");

    }

    public function registerAdmin(registerAdminRequest $registerAdminRequest)
    {
        $admin = DB::transaction(function () use ($registerAdminRequest) {
            $admin = $this->adminRepository->createAdmin($registerAdminRequest->validated());
            $admin->assignRole('super_admin');
            return $admin;
        });
        return $this->success('Admin Registered Successfully', [
            'token' => $admin->createToken('admin_token')->plainTextToken,
            'admin' => $admin
            ], 201);
    }

    public function loginAdmin(loginAdminRequest $loginAdminRequest)
    {
        $admin = $this->adminRepository->findByEmail($loginAdminRequest->email);

        if($admin &&  password_verify($loginAdminRequest->password, $admin->password))
        {
            return $this->success('Login Susseccfully', [
                'token' => $admin->createToken('admin_token')->plainTextToken,
                'admin' => $admin
            ], 200);
        }
        return $this->error('Informations are not Correct',null, 401);
    }

    public function logoutAdmin(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success("تم تسجيل الخروج بنجاح");

    }


    public function EditInformation(UpdateUserInformationRequest $request)
    {
        $user = $this->userRepository->update(auth()->user(), $request);

        return $this->success('success', ['user'=> $user]);
    }

    /**
     * Authenticate user and send verification code
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function login(LoginRequest $request)
    {

        $user = $this->userRepository->findByEmail($request->email);

        $this->authService->checkPassword($request->password, $user->password);

        $code = $this->codeService->generateCode($user);

        if (!event(new UserRegistered($user, $code))) {
            throw new CodeSendingException('فشل إرسال الكود');
        }

        return $this->success('تم إرسال كود التحقق');
    }

    /**
     * Resend verification code to user's email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function ResendCode(EmailRequest $request)
    {

        $user = $this->userRepository->findByEmail($request->email);

        $code = $this->codeService->generateCode($user);

        event(new UserRegistered($user, $code));

        return $this->success('تم إرسال الكود بنجاح');
    }

    /**
     * Verify user's code and activate account
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function verifyCode(EmailAndCodeRequest $request)
    {

        $user = $this->userRepository->findByEmail($request->email);

        $storedCode = $this->casheService->getCodeFromCashe($user);

        if (!$storedCode || $storedCode != $request->code) {
            throw new InvalidCodeException();
        }

        $this->userRepository->update($user, ['email_verified_at' => now()]);

        $this->casheService->forgetCodeFromCashe($user);

        $this->userRepository->deleteUserToken($user);

        $token = $this->userRepository->createToken($user);

        return $this->success('تم تفعيل الحساب بنجاح', ['token' => $token]);
    }

    /**
     * Refresh authentication token
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function refreshToken()
    {

        $user = auth()->user();

        $this->userRepository->deleteUserToken($user);

        $newToken = $this->userRepository->createToken($user);

        return $this->success("null", $newToken);
    }

    public function logout()
    {
        $user = auth()->user();
        // dd($user);
        $this->userRepository->deleteUserToken($user);
        return $this->success("تم تسجيل الخروج بنجاح");
    }

    public function getUser()
    {
        $user = auth()->user();
        return $this->success("success", $user);
    }

    public function storeFCM_Token(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $this->authService->storeFCM(auth()->user(),$request->input('fcm_token'));

        return $this->success("Token saved successfully");
    }
}
