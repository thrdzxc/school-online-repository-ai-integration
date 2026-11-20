<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WorkspaceSession
{
    public function handle(Request $request, Closure $next)
    {
        $workspace = $request->route('workspace');
        if (!$workspace) return $next($request);
        abort_unless(in_array($workspace, ['student', 'adviser', 'admin'], true), 404);
        $request->attributes->set('workspace', $workspace);
        // The workspace selects a session, not a controller/model argument.
        $request->route()->forgetParameter('workspace');
        $original = [
            'session.cookie' => config('session.cookie'),
            'session.path' => config('session.path'),
            'auth.guards.web.driver' => config('auth.guards.web.driver'),
        ];
        config([
            'session.cookie' => $original['session.cookie'].'_'.$workspace,
            'session.path' => $request->getBaseUrl().'/workspaces/'.$workspace,
            'auth.guards.web.driver' => 'workspace-session',
        ]);
        app('cookie')->setDefaultPathAndDomain(config('session.path'), config('session.domain'), config('session.secure'), config('session.same_site'));
        app('session')->forgetDrivers();
        app()->forgetInstance('session.store');
        auth()->forgetGuards();
        try {
            return $next($request);
        } finally {
            config($original);
            app('cookie')->setDefaultPathAndDomain(config('session.path'), config('session.domain'), config('session.secure'), config('session.same_site'));
            app('session')->forgetDrivers();
            app()->forgetInstance('session.store');
            auth()->forgetGuards();
        }
    }
}
