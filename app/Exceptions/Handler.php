<?php

namespace App\Exceptions;

//use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        //
    ];


    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException){
                if (request()->is('admin') || request()->is('admin/*')) {
                    return redirect()->route("get:admin:login")->with('error', 'You page session expired. Please try again');
                }
                if (request()->is('store-admin') || request()->is('store-admin/*')) {
                    return redirect()->route("get:store-admin:login")->with('error', 'You page session expired. Please try again');
                }
                if (request()->is('provider-admin') || request()->is('provider-admin/*')) {
                    return redirect()->route("get:provider-admin:login")->with('message', 'You page session expired. Please try again');
                }
                if (request()->is('billing-admin') || request()->is('billing-admin/*')) {
                    return redirect()->route("get:admin:login")->with('message', 'You page session expired. Please try again');
                }
                if (request()->is('dispatcher-admin') || request()->is('dispatcher-admin/*')) {
                    return redirect()->route("get:admin:login")->with('message', 'You page session expired. Please try again');
                }
                return redirect()->route('get:homepage')->with('error', 'You page session expired. Please try again');
            }
        });

        $this->renderable(function (\Exception $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                if (request()->is('admin') || request()->is('admin/*')) {
                    return redirect()->route("get:admin:login")->with('error', 'You page session expired. Please try again');
                }
                if (request()->is('store-admin') || request()->is('store-admin/*')) {
                    return redirect()->route("get:store-admin:login")->with('error', 'You page session expired. Please try again');
                }
                if (request()->is('provider-admin') || request()->is('provider-admin/*')) {
                    return redirect()->route("get:provider-admin:login")->with('message', 'You page session expired. Please try again');
                }
                if (request()->is('billing-admin') || request()->is('billing-admin/*')) {
                    return redirect()->route("get:admin:login")->with('message', 'You page session expired. Please try again');
                }
                if (request()->is('dispatcher-admin') || request()->is('dispatcher-admin/*')) {
                    return redirect()->route("get:admin:login")->with('message', 'You page session expired. Please try again');
                }
                return redirect()->route('get:homepage')->with('error', 'You page session expired. Please try again');
            };
        });
    }
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->guest(route("get:admin:login"));
        }
        if ($request->is('store-admin') || $request->is('store-admin/*')) {
            return redirect()->guest(route("get:store-admin:login"));
        }
        if ($request->is('provider-admin') || $request->is('provider-admin/*')) {
            return redirect()->guest(route("get:provider-admin:login"));
        }
        if ($request->is('billing-admin') || $request->is('billing-admin/*')) {
            return redirect()->guest(route("get:admin:login"));
        }
        if ($request->is('dispatcher-admin') || $request->is('dispatcher-admin/*')) {
            return redirect()->guest(route("get:admin:login"));
        }
        return redirect()->guest(route('get:homepage'));
    }
}
