<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session()->has('locale') && in_array(session('locale'), config('voyager.multilingual.locales'))) {
            App()->setLocale(session('locale'));
            //\Config::set('voyager.multilingual.default',session('locale'));
        }
        else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App()->setLocale( Auth::user() &&  Auth::user()->settings->has('locale')?Auth::user()->settings['locale']:config('voyager.multilingual.default'));
        }
        return $next($request);
    }
}
