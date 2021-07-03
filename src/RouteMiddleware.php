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
use Raylin666\Middleware\DelegateInterface;
use Raylin666\Middleware\Middleware;
use Raylin666\Http\Response;

/**
 * Class RouteMiddleware
 * @package Raylin666\Router
 */
class RouteMiddleware extends Middleware
{
    /**
     * @var Route
     */
    protected $route;

    /**
     * RouteMiddleware constructor.
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param DelegateInterface      $next
     * @return mixed
     */
    public function handler(ServerRequestInterface $serverRequest, DelegateInterface $next)
    {
        // TODO: Implement handler() method.

        if (is_string(($callback = $this->route->getCallback()))) {
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
            } else {
                $class = $callback;
                $method = 'handler';
            }
            $response = call_user_func_array([new $class, $method], [$serverRequest, $next]);
        } else {
            if (is_callable($callback)) {
                $response = call_user_func_array($callback, [$serverRequest, $next]);
            } else {
                if (is_array($callback)) {
                    $class = $callback[0];
                    if (is_string($class)) {
                        $class = new $class;
                    }
                    $response = call_user_func_array([$class, $callback[1]], [$serverRequest, $next]);
                } else {
                    $response = new Response('Don\'t support callback, Please setting callable function or class@method.');
                }
            }
        }
        unset($callback);
        return $response;
    }
}