<?php

namespace addons\placeholder;

use app\common\library\Menu;
use think\Addons;

/**
 * Placeholder
 */
class Placeholder extends Addons
{
    
    /**
     * app 初始化
     */
    public function appInit()
    {
        //\think\Route::rule('image/:size', 'addons/placeholder/index/direct', 'GET')->pattern(['size' => '[^/]+']);
        
        // 支持 /image/250x300.png 格式
        //\think\Route::rule('/image/:size', "\\think\\addons\\Route@execute?addon=placeholder&controller=index&action=direct&size=:size");
        
        // 配置更灵活的路由规则，支持无后缀格式
        /*\think\Route::rule('/image/:width/:height', "\\think\\addons\\Route@execute?addon=placeholder&controller=index&action=image&width=:width&height=:height");
        \think\Route::rule('/image/:width/:height.:format', "\\think\\addons\\Route@execute?addon=placeholder&controller=index&action=image&width=:width&height=:height&format=:format");*/
    }

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('placeholder');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('placeholder');
    }

}
