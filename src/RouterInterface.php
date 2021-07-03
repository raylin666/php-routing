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

/**
 * Interface RouterInterface
 * @package Raylin666\Router
 */
interface RouterInterface
{
    /**
     * @param string $method
     * @return mixed
     */
    public function withMethod(string $method);

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param null $callback
     * @return mixed
     */
    public function withCallback($callback = null);

    /**
     * @return mixed
     */
    public function getCallback();

    /**
     * @param array $parameters
     * @return mixed
     */
    public function withParameters(array $parameters);

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param $middleware
     * @return mixed
     */
    public function withMiddleware($middleware);

    /**
     * @param $middleware
     * @return mixed
     */
    public function withAddMiddleware($middleware);

    /**
     * @return array
     */
    public function getMiddleware(): array;
}