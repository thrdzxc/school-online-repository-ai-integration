<?php

namespace App\Support;

use Illuminate\Routing\UrlGenerator;

class WorkspaceUrlGenerator extends UrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        $workspace = $this->request->attributes->get('workspace');
        if ($workspace && is_string($name) && !str_starts_with($name, 'workspace.')
            && $this->routes->getByName('workspace.'.$name)) {
            $name = 'workspace.'.$name;
            $this->defaults(['workspace' => $workspace]);
        }
        return parent::route($name, $parameters, $absolute);
    }

    /** Store notification destinations independently of the sender's workspace. */
    public static function destination(string $url, ?string $workspace = null): string
    {
        $root = request()->getSchemeAndHttpHost().request()->getBaseUrl();
        if (str_starts_with($url, $root.'/')) {
            $path = substr($url, strlen($root));
        } elseif (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $path = $url;
        } else {
            return $url;
        }
        $path = preg_replace('#^/workspaces/(student|adviser|admin)(?=/|$)#', '', $path);
        return $root.($workspace ? '/workspaces/'.$workspace : '').$path;
    }
}
