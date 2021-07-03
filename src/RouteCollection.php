<?php
// +----------------------------------------------------------------------
// | Created by linshan. 版权所有 @
// +----------------------------------------------------------------------
// | Copyright (c) 2021 All rights reserved.
// +----------------------------------------------------------------------
// | Technology changes the world . Accumulation makes people grow .
// +----------------------------------------------------------------------
// | Author: kaka梦很美 <1099013371@qq.com>
// +----------------------------------------------------------------------

namespace Raylin666\Router;

use Psr\Http\Message\ServerRequestInterface;
use Raylin666\Http\Message\Request;

/**
 * Class RouteCollection
 * @package Raylin666\Router
 */
class RouteCollection
{
    /**
     * 路由分块
     */
    const ROUTES_CHUNK = 10;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @var Route
     */
    protected $activeRoute;

    /**
     * @var Route[]
     */
    public $staticRoutes = [];

    /**
     * @var Route[]
     */
    public $dynamicRoutes = [];

    /**
     * @var array
     */
    public $aliasMap = [];

    /**
     * @var int
     */
    protected $num = 1;

    /**
     * 路由分组计数器
     *
     * @var int
     */
    protected $index = 0;

    /**
     * @var array
     */
    protected $regexes = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * RouteCollection constructor.
     * @param null $namespace
     */
    public function __construct($namespace = null)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param          $path
     * @param callable $callback
     * @return RouteCollection
     */
    public function group($path, callable $callback): RouteCollection
    {
        $middleware = $this->middleware;
        if (is_array($path)) {
            $middlewareOptions = isset($path['middleware']) ? $path['middleware'] : [];
            if (is_array($middlewareOptions)) {
                $this->middleware = array_merge($this->middleware, $middlewareOptions);
            }  else {
                $this->middleware[] = $middlewareOptions;
            }
            $path = isset($path['prefix']) ? $path['prefix'] : '';
        }

        array_push($this->with, $path);

        $callback($this);

        array_pop($this->with);
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * @param $middleware
     * @param callable $callback
     * @return RouteCollection
     */
    public function middleware($middleware, callable $callback): RouteCollection
    {
        array_push($this->middleware, $middleware);
        $callback($this);
        array_pop($this->middleware);
        return $this;
    }

    /**
     * @param $callback
     * @return string
     */
    protected function concat($callback)
    {
        return ! is_string($callback) ? $callback : $this->namespace . $callback;
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function get($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_GET, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function post($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_POST, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function put($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_PUT, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function delete($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_DELETE, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function head($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_HEAD, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function options($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_OPTIONS, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param $path
     * @param $callback
     * @param array $defaults
     * @return Route
     */
    public function patch($path, $callback, array $defaults = []): Route
    {
        return $this->addRoute(Request::METHOD_PATCH, $path, $this->concat($callback), $defaults);
    }

    /**
     * @param null $name
     * @return bool|Route
     */
    public function getRoute($name = null)
    {
        if (is_null($name)) {
            return $this->aliasMap;
        }

        foreach ($this->aliasMap as $method => $routes) {
            if (! isset($routes[$name])) {
                continue ;
            }

            return $routes[$name];
        }

        return false;
    }

    /**
     * @return Route
     */
    public function getActiveRoute()
    {
        return $this->activeRoute;
    }

    /**
     * 创建路由
     *
     * @param $method
     * @param $path
     * @param $callback
     * @return Route
     */
    public function createRoute($method, $path, $callback): Route
    {
        return new Route($method, $path, $callback);
    }

    /**
     * 添加路由
     *
     * @param string $method
     * @param $path
     * @param $callback
     * @return Route
     */
    public function addRoute(string $method, $path, $callback)
    {
        if (is_array($path)) {
            $name = $path['name'];
            $path = implode('/', $this->with) . $path['path'];
        } else {
            $name = $path = implode('/', $this->with) . $path;
        }

        if (isset($this->aliasMap[$method][$name])) {
            return $this->aliasMap[$method][$name];
        }

        $route = $this->createRoute($method, $path, $callback);
        $route->withAddMiddleware($this->middleware);
        if ($route->isStatic()) {
            $this->staticRoutes[$method][$path] = $route;
        } else {
            $this->dynamicRoutes[$method][] = $route;
        }

        $this->aliasMap[$method][$name] = $route;
        return $route;
    }

    /**
     * 匹配路由
     *
     * @param ServerRequestInterface $serverRequest
     * @return Route
     * @throws RouteNotFoundException
     */
    public function match(ServerRequestInterface $serverRequest)
    {
        $method = $serverRequest->getMethod();
        $path = $serverRequest->getUri()->getPath();
        if (isset($this->staticRoutes[$method][$path])) {
            return $this->activeRoute = $this->staticRoutes[$method][$path];
        } else {
            $possiblePath = $path;
            if ('/' === substr($possiblePath, -1)) {
                $possiblePath = rtrim($possiblePath, '/');
            } else {
                $possiblePath .= '/';
            }
            if (isset($this->staticRoutes[$method][$possiblePath])) {
                return $this->activeRoute = $this->staticRoutes[$method][$possiblePath];
            }
            unset($possiblePath);
        }

        if (
            ! isset($this->dynamicRoutes[$method])
            || false === $route = $this->matchDynamicRoute($serverRequest, $method, $path)
        ) {
            throw new RouteNotFoundException($path);
        }

        return $this->activeRoute = $route;
    }

    /**
     * 匹配动态路由
     *
     * @param ServerRequestInterface $serverRequest
     * @param string $method
     * @param string $path
     * @return bool|Route
     */
    protected function matchDynamicRoute(ServerRequestInterface $serverRequest, $method, $path)
    {
        /** @var Route $route */
        foreach ($this->dynamicRoutes[$method] as $route) {
            if (! preg_match('~^' . $route->getRegex() . '$~', $path, $matches)) {
                continue;
            }
            $match = array_slice($matches, 1, count($route->getVariables()));
            $attributes = array_combine($route->getVariables(), $match);
            $attributes = array_filter($attributes);
            $route->mergeParameters($attributes);
            foreach ($route->getParameters() as $key => $attribute) {
                $serverRequest->withAttribute($key, $attribute);
            }

            return $route;
        }

        return false;
    }

    /**
     * 生成Url地址
     *
     * @param $name
     * @param array $parameters
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function generateUrl($name, array $parameters = [], $format = '')
    {
        if (false === ($route = $this->getRoute($name))) {
            throw new RouteNotFoundException($name);
        }

        $format = ( ! empty($format)) ? '.' . $format : '';

        if ($route->isStaticRoute()) {
            return $route->getPath() . $format;
        }

        $parameters = array_merge($route->getParameters(), $parameters);
        $queryString = [];

        foreach ($parameters as $key => $parameter) {
            if ( ! in_array($key, $route->getVariables())) {
                $queryString[$key] = $parameter;
                unset($parameters[$key]);
            }
        }

        $search = array_map(function ($v) {
            return '{' . $v . '}';
        }, array_keys($parameters));

        $replace = $parameters;

        $path = str_replace($search, $replace, $route->getPath());

        if (false !== strpos($path, '[')) {
            $path = str_replace(['[', ']'], '', $path);
            $path = rtrim(preg_replace('~(({.*?}))~', '', $path), '/');
        }

        return $path . $format . ([] === $queryString ? '' : '?' . http_build_query($queryString));
    }
}