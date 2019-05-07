<?php

namespace Cyanobacteria\Route;

use App\Repositories\OauthClientRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Schema;


class LaravelSubRoutes extends RouteServiceProvider
{

    private $configPath;
    private $prefix;
    private $subRouteFolderName;
    private $middlewareType;
    private $middlewares;
    private $oauthClients;
    private $middlewareRules;
    private $ignoreList;
    private $noPrefix = false;

    public function __construct($params)
    {
        $this->middlewareType = $params['middlewareType'];
        $this->setMiddlewareType();
    }

    public function setNoPrefixTrue()
    {
        $this->noPrefix = true;
        return $this;
    }

    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
        return $this;
    }

    public function setPrefix($prifix)
    {
        $this->prefix = $prifix;
        return $this;
    }

    public function setSubRouteFolderName($subRouteFolderName)
    {
        $this->subRouteFolderName = $subRouteFolderName;
        return $this;
    }

    public function setSubRouteFileName($subRouteFileName)
    {
        $this->subRouteFileName = $subRouteFileName;
        return $this;
    }

    public function setMiddlewares($middleware)
    {
        $this->middlewares = $middleware;
        return $this;
    }

    public function setIgnoreList($list)
    {
        $this->ignoreList = $list;
        return $this;
    }

    /*
     * there are some main logic as follow ...
     * 1.subRoutefolderName
     * 2.filesName
     * 3.
     *
     * 哪些是必要值？
     * 沒有middleware
     */

    private function anyEmpty($Ary)
    {
        foreach ($Ary as $value) if (empty($value)) return true;
        return false;
    }

    private function allEmpty($Ary)
    {
        foreach ($Ary as $value) if (!empty($value)) return false;
        return true;
    }

    private function setMiddlewareType()
    {

        switch ($this->middlewareType) {
            case 'funcity':
                $tableEmpty = empty(Schema::hasTable('oauth_clients'));
                if ($tableEmpty) {
                    $this->oauthClients = null;
                } else {
                    $ary = OauthClientRepository::getAll();
                    $newAry = array();
                    foreach ($ary as $k => $v) $newAry[$v->merchant_code] = $v;
                    $this->oauthClients = (empty($newAry)) ? null : $newAry;
                }
                $this->middlewareRules = $this->getSubRouteMiddlewareRules();//get middleware rule
                break;

            case 'default':

                break;

            //must has value

        }
    }

//    private $configPath;

//    private $prefix;

//    private $subRouteFolderName;


//    private $middlewareType;
//    private $oauthClients;
//    private $middlewareRules;

//    private $middlewares;


    public function build()
    {
        if (empty($this->subRouteFolderName)) return null;//大批引入必須要有子資料夾
        foreach ($this->getSubRouteFileList(['subRouteFolderName' => $this->subRouteFolderName]) as $fileCompletePath) {
            $fileName = $this->getFileNameForSubRoutes($fileCompletePath);

            $middlewares = $this->getSubRouteMiddleware($fileName);

            $route = $this->addPrefix($fileName);

            $route = $this->addSubRouteMiddleware([
                'route' => $route,
                'middlewares' => $middlewares
            ]);
            if (empty($route)) {
                Route::namespace($this->namespace)->group($fileCompletePath);
            } else {
                $route->namespace($this->namespace)->group($fileCompletePath);
            }
        }


    }

    private function addPrefix($fileName)
    {
        if ($this->noPrefix) return null;
        $prefix = (!empty($this->prefix)) ? "{$this->prefix}/$fileName" : $fileName;
        return Route::prefix($prefix);

    }

    private function addSubRouteMiddleware($params)
    {
        [$route, $middleware] = [$params['route'], $params['middlewares']];
        if (empty($route)) return $route;
        if (empty($middleware)) return $route;
        return $route->middleware($middleware);

    }
//    private $middlewareType;
//    private $oauthClients;
//    private $middlewareRules;

//    private $middlewares;
    private function getSubRouteMiddleware($fileName)
    {
        /*
         * 需要兩種實現
         * 1.自己指定
         * 2.根據設定還有db
         */
        $fromConfig = !$this->anyEmpty([
            $this->middlewareRules,
            $this->middlewareType,
            $this->oauthClients,
        ]);
        $set = !empty($this->middlewares);
        $noMiddleware = (empty($fromConfig) and empty($set)) ? true : false;
        $tableEmpty = empty(Schema::hasTable('oauth_clients'));
        if ($noMiddleware) {
            return null;
        } elseif ($tableEmpty and !$set) {
            return null;
        } elseif ($fromConfig) {
            $rules = $this->getSubRouteMiddlewareRules();//get middleware rule
            if (empty($this->oauthClients)) return null; //if null return null
            $oauthClientId=$this->oauthClients[$fileName];
            if (empty($rules[$oauthClientId])) return null; //if null return null
            return $rules[$oauthClientId]; //return middlewares
        } elseif ($set) {
            return $set;
        }

    }

    private function getSubRouteMiddlewareRules()
    {
        // config
        $merchantsMiddleware = config($this->configPath . '.middlewares');
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

