<?php

namespace Snowfox\Router;

interface ParamsDelegateInterface
{
    public function getRouterParam(string $key): string;
}
