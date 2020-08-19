<?php
namespace Dongdavid\Notify;

use Dongdavid\Notify\utils\Redis;

// 如果没有cache方法就自定义一个方法用redis做缓存
if (!function_exists('cache')) {
    function cache($name, $value = null)
    {
        if (is_array($name)) {
            // name 若传入为数组，则当作redis链接配置
            Redis::setConfig($name);
        } else {
            if ($value === null) {
                return Redis::get($name);
            } else {
                Redis::set($name, $value);
            }
        }
    }
} else {
    function cache($name, $value = null)
    {
        return \cache($name, $value);
    }
}

if (!function_exists('config')) {
    function config($name, $value = null)
    {
        if (null !== $value) {
            \Dongdavid\Notify\cache('notify-config', $value);
        } else {
            return \Dongdavid\Notify\cache('notify-config');
        }
    }
} else {
    function config($name, $value = null)
    {
        return \config($name, $value);
    }
}