<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Modules\Sale\Entities\VisitorStat;

class VisitorCounter
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $visitedPage = $request->path();
        $visitedAt = Carbon::now();

        $current_date = $visitedAt->toDateString();

         $statMatchByDate = VisitorStat::whereDate('visited_at', $current_date)
             ->where('ip_address',$ip)
             ->exists();

         if(!$statMatchByDate){
             VisitorStat::create([
                 'ip_address'   => $ip,
                 'user_agent'   => $userAgent,
                 'visited_at'   => $visitedAt,
                 'visited_page' => $visitedPage,
             ]);
         }

        return $next($request);
    }
}
