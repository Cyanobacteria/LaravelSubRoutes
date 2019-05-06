<?php

namespace Cyanobacteria\Route;
use App\Repositories\OauthClientRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Schema;


class LaravelSubRoutes extends RouteServiceProvider{

    private $configPath;
    //private $subFolderName;
    public function __construct($params)
    {
        $this->configPath=$params['configPath'];
    }

    public function mapSubRoutes($params)
    {
        foreach ($this->getSubRouteFileList(['subRouteFolderName'=>$params['subRouteFolderName']]) as $fileCompletePath) {
            $fileName = $this->getFileNameForSubRoutes($fileCompletePath);

            $middlewares = $this->getSubRouteMiddleware($fileName);

            $route = Route::prefix($fileName);

            $route = $this->addSubRouteMiddleware([
                'route'=>$route,
                'middlewares'=>$middlewares
            ]);

            $route->namespace($this->namespace)->group($fileCompletePath);

        }
    }

    private function addSubRouteMiddleware($params)
    {
        [$route, $middleware] = [$params['route'], $params['middlewares']];
        if (empty($middleware)) return $route;
        foreach ($middleware as $mw) $route=$route->middleware($mw);
        return $route;
    }

    private function getSubRouteMiddleware($fileName)
    {
        if(empty(Schema::hasTable('oauth_clients'))) return null;
        $oauthClient = OauthClientRepository::getOneByMerchantCode(['merchant_code'=>$fileName]);
        $rules = $this->getSubRouteMiddlewareRules();//get middleware rule
        if (empty($oauthClient)) return null; //if null return null
        if (empty($rules[$oauthClient->id])) return null; //if null return null
        return $rules[$oauthClient->id]; //return middlewares
    }

    private function getSubRouteMiddlewareRules()
    {
        // config
        $merchantsMiddleware = config($this->configPath.'.middlewares');
        return $merchantsMiddleware;

    }

    private function getSubRouteFileList($params)
    {
        return glob(base_path("routes/{$params['subRouteFolderName']}") . '/*.php');
    }

    private function getFileNameForSubRoutes($file)
    {

        $prefix = explode('/', $file);
        $prefix = end($prefix);
        $prefix = explode('.', $prefix);
        return array_shift($prefix);
    }






}

