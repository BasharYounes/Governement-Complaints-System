<?php

namespace App\Exceptions;

use App\Exceptions\Content\Audiobook\AudioRegistrationException;
use App\Exceptions\Content\ContentRegistrationException;
use App\Exceptions\Content\Podcast\PodcastRegistrationException;
use App\Exceptions\FileStorageException;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * Register custom exception handling.
     */
    public function register(): void
    {
    //     $this->renderable(function (RegistrationFailedException $e) {
    //         return $e->render();
    //     });

    //     $this->renderable(function (CodeSendingException $e) {
    //         return $e->render();
    //     });

    //     $this->renderable(function (InvalidCodeException $e) {
    //         return $e->render();
    //     });

    //     $this->renderable(function (InvalidCredentialsException $e) {
    //         return $e->render();
    //     });

    //     $this->renderable(function(FileStorageException $e){
    //         return $e->render();
    //     });

    //     $this->renderable(function(AudioRegistrationException $e){
    //         return $e->render();
    //     });

    //     $this->renderable(function(PodcastRegistrationException $e){
    //         return $e->render();
    //     });

    //     $this->renderable(function(ContentRegistrationException $e){
    //         return $e->render();
    //     });

    //     $this->renderable(function (Throwable $e, $request) {
    //         return response()->view('errors.custom', [], 500);
    //     });

    //     $this->renderable(function (Exception $e) {
    //         return response()->json([
    //             'message' => 'حدث خطأ غير متوقع',
    //             'error' =>  $e->getMessage()
    //         ], 500);
    //     });
    }

    public function render($request, Throwable $e)
    {
        switch (true) {
            case $e instanceof RegistrationFailedException:
                return $e->render();

            case $e instanceof CodeSendingException:
                return $e->render();

            case $e instanceof InvalidCodeException:
                return $e->render();

            case $e instanceof InvalidCredentialsException:
                return $e->render();
            case $e instanceof AuthenticationException
                :
                return $this->error('غير مصرح', $e->getMessage(), 401);

            case $e instanceof Exception:
                return $this->error(
                    'حدث خطأ غير متوقع',
                     $e->getMessage()
                , 500);

            default:
                return $this->error('حدثت مشكلة أعد المحاولة بعد معرفة الخطأ',$e->getMessage());
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    // protected function unauthenticated($request, AuthenticationException $exception)
    // {
    //     if ($request->expectsJson() || $request->is('api/*')) {
    //         return $this->error('غير مصرح', 'Unauthenticated', 401);
    //     }

    //     // Fallback to a simple login URL (avoid route('login') if it's not defined)
    //     return redirect()->guest(url('/login'));
    // }
}
