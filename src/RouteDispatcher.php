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

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Raylin666\Middleware\Dispatcher;
use Raylin666\Middleware\MiddlewareInterface;
use RuntimeException;

/**
 * Class RouteDispatcher
 * @package Raylin666\Router
 */
class RouteDispatcher extends Dispatcher implements RouterDispatcherInterface
{
    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @var array
     */
    protected $definition = [];

    /**
     * @var array
     */
    protected $appendMiddleware = [];

    /**
     * RouteDispatcher constructor.
     *
     * @param RouteCollection $routeCollection
     * @param $definition
     */
    public function __construct(RouteCollection $routeCollection, $definition = [])
    {
        $this->routeCollection = $routeCollection;
        $this->definition = $definition;
        parent::__construct([]);
    }

    /**
     * @param $name
     * @param $middleware
     * @return $this
     */
    public function addDefinition($name, $middleware): RouteDispatcher
    {
        if (isset($this->definition[$name])) {
            if (is_array($this->definition[$name])) {
                $this->definition[$name][] = $middleware;
            } else {
                $this->definition[$name] = [
                    $this->definition[$name],
                    $middleware,
                ];
            }
        } else {
            $this->definition[$name] = $middleware;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $route = $this->routeCollection->match($request);

        foreach ($this->appendMiddleware as $middleware) {
            $route->withAddMiddleware($middleware);
        }

        return $this->callMiddleware($route, $request);
    }

    /**
     * @param Route $route
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function callMiddleware(Route $route, ServerRequestInterface $request)
    {
        $prototypeStack = clone $this->stack;

        foreach ($route->getMiddleware() as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $this->before($middleware);
            } else {
                if (is_string($middleware)) {
                    if (class_exists($middleware)) {
                        $this->before(new $middleware);
                    } elseif (isset($this->definition[$middleware])) {
                        $definition = $this->definition[$middleware];
                        if (is_array($definition)) {
                            foreach ($definition as $value) {
                                $this->before(is_string($value) ? new $value : $value);
                            }
                        } else {
                            $this->before(is_string($definition) ? new $definition : $definition);
                        }
                    } else {
                        throw new RuntimeException(sprintf('Middleware %s is not defined.', $middleware));
                    }
                } else {
                    throw new RouteException(sprintf('Don\'t support %s middleware', gettype($middleware)));
                }
            }
        }

        // wrapper route middleware
        $this->before(new RouteMiddleware($route));

        try {
            $response = parent::dispatch($request);
            $this->stack = $prototypeStack;
            unset($prototypeStack);
        } catch (Exception $exception) {
            $this->stack = $prototypeStack;
            unset($prototypeStack);
            throw $exception;
        }

        return $response;
    }
}