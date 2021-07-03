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

use Raylin666\Http\Message\Response;

/**
 * Class RouteNotFoundException
 * @package Raylin666\Router
 */
class RouteNotFoundException extends RouteException
{
    /**
     * RouteNotFoundException constructor.
     * @param $path
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('Route "%s" is not found.', $path), Response::HTTP_NOT_FOUND, null);
    }
}