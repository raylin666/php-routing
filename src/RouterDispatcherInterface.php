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

/**
 * Interface RouterDispatcherInterface
 * @package Raylin666\Router
 */
interface RouterDispatcherInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function dispatch(ServerRequestInterface $request);
}