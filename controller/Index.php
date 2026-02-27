<?php
/**
 * Placeholder插件控制器
 */
namespace addons\placeholder\controller;

use addons\placeholder\library\Placeholder;
use think\addons\Controller;
use think\Db;
use think\Session;

class Index extends Controller
{
    // 插件配置
    protected $config;
    
    /**
     * 初始化方法
     */
    public function _initialize()
    {
        parent::_initialize();
        
        // 加载插件配置
        $this->config = get_addon_config($this->addon);
    }
    
    /**
     * 插件首页
     * @return mixed
     */
    public function index()
    {
        // 渲染模板
        if($this->request->url() != addon_url($this->addon.'/index/index')){
            header('Location: ' . addon_url($this->addon.'/index/index'));
            exit;
        }

        $this->assign('config', $this->config);
        $this->assign('baseUrl', rtrim(url(explode('[:size]', $this->config['rewrite']['index/image'])[0],'',false,true), '/'));
        return $this->fetch();
    }
    
    /**
     * 生成图片
     * 支持直接URL路径参数，如 /addons/placeholder/index/image/250x300.png
     * @return void
     */
    public function image()
    {
        // 清除可能的输出缓冲
        //ob_clean();
        
        // 尝试从URL路径中解析尺寸和格式参数
        $paramsPath = $this->parsePathParams();
        
        // 尝试从URL参数中解析尺寸和格式参数
        $paramsUrl = $this->parseUrlParams();
        $params = array_merge($paramsPath, $paramsUrl);
        
        // 创建Placeholder实例并生成图片
        $placeholder = new Placeholder($this->config);
        $placeholder->generate($params);
        
        // 确保后续不再有输出
        exit;
    }
    
    
    /**
     * 从URL路径中解析
     */
    private function parsePathParams()
    {
        // 获取当前请求的路径信息
        $pathInfo = $this->request->pathinfo();
        $param = $this->request->param();
        $rePathInfo = str_replace(ltrim(explode('/[:size]', $this->config['rewrite']['index/image'])[0], '/'), '', $pathInfo);
        $result = [];

        // 按 '/' 分割，过滤空段
        $segments = array_filter(explode('/', trim($rePathInfo, '/')));
        if (empty($segments)) {
            return $result;
        }
        // ---------- 1. 解析尺寸（必选）----------
        $first = array_shift($segments);
        if($first == 'addons') {
            unset($param['addon'], $param['controller'], $param['action'], $param['size']);
            $first = array_pop($segments);
        }
        unset($param['size']);
        // 情况A：第一个段内嵌分隔符（x、*）或直接就是数字（含扩展名）
        if (preg_match('/^(\d+)(?:[xX*](\d+))?(?:\.([a-zA-Z0-9]+))?$/', $first, $match)) {
            // 例：600 / 600x400 / 600*400 / 600.jpg / 600x400.png
            $result['width'] = $match[1];
            // 高度：若分隔符后存在数字则取之，否则等于宽度
            $result['height'] = isset($match[2]) ? $match[2] : $match[1];
            // 格式：若文件名内嵌扩展名则提取
            if (isset($match[3])) {
                $result['format'] = strtolower($match[3]);
            }
        }
        // 情况B：第一个段是纯数字（宽度），需要从下一个段获取高度
        elseif (preg_match('/^(\d+)$/', $first, $match)) {
            $result['width'] = $match[1];
            $next = $segments[0] ?? null;
    
            // 下一个段存在且是纯数字（或数字.扩展名）
            if ($next && preg_match('/^(\d+)(?:\.([a-zA-Z0-9]+))?$/', $next, $nextMatch)) {
                // 消耗下一个段
                array_shift($segments);
                $result['height'] = $nextMatch[1];
                // 格式可能内嵌在高度段中
                if (isset($nextMatch[2])) {
                    $result['format'] = strtolower($nextMatch[2]);
                }
            } else {
                // 没有高度段，高度等于宽度
                $result['height'] = $result['width'];
            }
        }
        // 情况C：第一个段不符合任何尺寸格式 → 可抛出异常或直接返回空
        else {
            // 按需求可记录错误
            return [];
        }
        return array_merge($param, $result);
    }
    
    /**
     * 从URL参数中解析
     */
    private function parseUrlParams()
    {
        // 获取GET请求参数
        $result = $this->request->only(['width', 'height', 'format', 'text', 'bgcolor', 'fcolor', 'textcolor', 'grayscale', 'blur'], 'get');
        if(isset($result['fcolor']) && !isset($result['textcolor'])) $result['textcolor'] = $result['fcolor'];
        if(isset($result['format'])) $result['format'] = strtolower($result['format']);
        return $result;
    }
}