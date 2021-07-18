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

use Closure;
use Raylin666\Http\Message\Request;

/**
 * Class Route
 * @package Raylin666\Router
 */
class Route extends RouteRegex implements RouterInterface
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $method = Request::METHOD_GET;

    /**
     * @var mixed
     */
    protected $callback;

    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * Route constructor.
     *
     * @param string $method
     * @param $path
     * @param $callback
     */
    public function __construct(string $method, $path, $callback)
    {
        parent::__construct($path);
        $this->withMethod($method);
        $this->withCallback($callback);
    }

    /**
     * @param string $method
     * @return $this
     */
    public function withMethod(string $method): Route
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param callable|null $callback
     * @return $this
     */
    public function withCallback($callback = null): Route
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return Closure|string|null
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function withParameters(array $parameters): Route
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function mergeParameters(array $parameters): Route
    {
        $this->parameters = array_merge($this->parameters, array_filter($parameters));
        return $this;
    }

    /**
     * @param $middleware
     * @return $this
     */
    public function withMiddleware($middleware): Route
    {
        $this->middleware = [$middleware];
        return $this;
    }

    /**
     * @param $middleware
     * @return $this
     */
    public function withAddMiddleware($middleware): Route
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}