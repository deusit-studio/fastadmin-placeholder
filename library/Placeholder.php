<?php
/**
 * Placeholder图片生成类
 * 用于生成自定义占位图片
 */
namespace addons\placeholder\library;

class Placeholder
{
    // 图片默认配置
    private $defaultConfig = [
        'width' => 300,
        'height' => 200,
        'bgcolor' => 'CCCCCC',
        'textcolor' => '666666',
        'text' => '',
        'format' => 'png',
        'grayscale' => false,
        'blur' => 0,
    ];
    
    // 插件配置
    private $pluginConfig;
    
    /**
     * 构造函数
     * @param array $pluginConfig 插件配置
     */
    public function __construct($pluginConfig = [])
    {
        $this->pluginConfig = $pluginConfig;
    }
    
    /**
     * 生成占位图片
     * @param array $config 图片配置
     */
    public function generate($config = [])
    {
        // 合并配置
        $config = array_merge($this->defaultConfig, $config);
        
        // 验证和处理配置
        $this->validateConfig($config);
        
        // 创建图片
        $image = $this->createImage($config);

        // 如果指定了图片URL，加载图片作为背景
        if (isset($config['image_url']) && $this->pluginConfig['remote_allow']) {
            $this->loadBackgroundImage($image, $config['image_url'], $config['width'], $config['height']);
        }
        // 添加文字
        $this->addText($image, $config);
        
        // 添加文字
        $this->addText($image, $config);
        
        // 应用特效
        $this->applyEffects($image, $config);
        
        // 输出图片
        $this->outputImage($image, $config);
        
        // 释放资源
        imagedestroy($image);
    }
    
    /**
     * 验证和处理配置
     * @param array $config 图片配置
     */
    private function validateConfig(&$config)
    {
        // 验证尺寸
        $maxWidth = isset($this->pluginConfig['max_width']) ? $this->pluginConfig['max_width'] : 2000;
        $maxHeight = isset($this->pluginConfig['max_height']) ? $this->pluginConfig['max_height'] : 2000;
        
        $config['width'] = max(10, min($maxWidth, intval($config['width'])));
        $config['height'] = max(10, min($maxHeight, intval($config['height'])));
        
        // 验证颜色
        $config['bgcolor'] = $this->validateColor($config['bgcolor'], 'CCCCCC');
        $config['textcolor'] = $this->validateColor($config['textcolor'], '666666');
        
        // 验证格式
        $allowFormats = ['jpg', 'png', 'gif']; // 默认允许的格式
        if (isset($this->pluginConfig['allow_formats'])) {
            if (is_array($this->pluginConfig['allow_formats'])) {
                $allowFormats = $this->pluginConfig['allow_formats'];
            } elseif (is_string($this->pluginConfig['allow_formats'])) {
                // 如果是字符串，尝试按逗号分隔
                $allowFormats = array_filter(array_map('trim', explode(',', $this->pluginConfig['allow_formats'])));
            }
        }
        $config['format'] = strtolower($config['format']);
        if (!in_array($config['format'], $allowFormats)) {
            $config['format'] = 'png';
        }
        
        // 验证模糊度
        $maxBlur = isset($this->pluginConfig['max_blur']) ? $this->pluginConfig['max_blur'] : 100;
        $config['blur'] = max(0, min($maxBlur, intval($config['blur'])));
        
        // 处理文字
        if (empty($config['text'])) {
            $config['text'] = "{$config['width']} × {$config['height']}";
        }else{
            $config['text'] = str_replace('+', ' ', $config['text']);
        }
        
        // 处理布尔值
        $config['grayscale'] = filter_var($config['grayscale'], FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * 验证颜色值
     * @param string $color 颜色值
     * @param string $default 默认颜色
     * @return string 验证后的颜色值
     */
    private function validateColor($color, $default)
    {
        // 移除可能的 # 前缀
        $color = str_replace('#', '', $color);

        // 匹配 3 位或 6 位十六进制颜色
        if (preg_match('/^[0-9A-Fa-f]{3}$/', $color)) {
            // 3 位简写：每一位重复一次，扩展为 6 位
            return $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        } elseif (preg_match('/^[0-9A-Fa-f]{6}$/', $color)) {
            // 6 位标准格式，直接返回
            return $color;
        }

        // 无效格式，返回默认值
        return $default;
    }
    
    /**
     * 创建图片
     * @param array $config 图片配置
     * @return resource 图片资源
     */
    private function createImage($config)
    {
        $image = imagecreatetruecolor($config['width'], $config['height']);
        
        // 设置背景色
        $bgColor = $this->hexToRgb($config['bgcolor']);
        $bg = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefilledrectangle($image, 0, 0, $config['width'] - 1, $config['height'] - 1, $bg);
        
        // 添加边框
        $borderColor = $this->hexToRgb($this->adjustBrightness($config['bgcolor'], -20));
        $border = imagecolorallocate($image, $borderColor['r'], $borderColor['g'], $borderColor['b']);
        imagerectangle($image, 0, 0, $config['width'] - 1, $config['height'] - 1, $border);
        
        return $image;
    }

    /**
     * 加载背景图片
     * @param resource $image 目标图像资源
     * @param string $imageUrl 图片URL
     * @param int $width 目标宽度
     * @param int $height 目标高度
     * @param bool $fit 是否等比例缩放
     */
    private function loadBackgroundImage(&$image, $imageUrl, $width, $height, $fit=false)
    {
        try {
            // 验证URL格式
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                return;
            }
            
            // 尝试获取远程图片
            $context = stream_context_create([
                'http' => [
                    'timeout' => $this->pluginConfig['remote_timeout']
                ]
            ]);
            $imgData = @file_get_contents($imageUrl, false, $context);
            
            if ($imgData) {
                $temp = @imagecreatefromstring($imgData);
                if ($temp) {
                    $tempWidth = imagesx($temp);
                    $tempHeight = imagesy($temp);
                    
                    if ($fit) {
                        // 等比例缩放
                        $ratio = min($width / $tempWidth, $height / $tempHeight);
                        $newWidth = $tempWidth * $ratio;
                        $newHeight = $tempHeight * $ratio;
                        $x = ($width - $newWidth) / 2;
                        $y = ($height - $newHeight) / 2;
                        
                        imagecopyresampled($image, $temp, $x, $y, 0, 0, $newWidth, $newHeight, $tempWidth, $tempHeight);
                    } else {
                        // 拉伸填充
                        imagecopyresampled($image, $temp, 0, 0, 0, 0, $width, $height, $tempWidth, $tempHeight);
                    }
                    
                    imagedestroy($temp);
                }
            }
        } catch (\Exception $e) {
            // 忽略错误，继续使用纯色背景
        }
    }
    
    /**
     * 添加文字
     * @param resource $image 图片资源
     * @param array $config 图片配置
     */
    private function addText($image, $config)
    {
        // 设置文字颜色
        $textColor = $this->hexToRgb($config['textcolor']);
        $text = imagecolorallocate($image, $textColor['r'], $textColor['g'], $textColor['b']);
        
        // 获取字体路径
        $fontPath = $this->getFontPath();
        
        // 判断是否使用内置字体
        if (is_numeric($fontPath)) {
            // 使用GD内置字体
            $fontSize = $fontPath; // 内置字体大小为1-5
            
            // 计算文字大小和位置
            $textWidth = imagefontwidth($fontSize) * strlen($config['text']);
            $textHeight = imagefontheight($fontSize);
            
            $x = intval(($config['width'] - $textWidth) / 2);
            $y = intval(($config['height'] - $textHeight) / 2);
            
            // 添加文字
            imagestring($image, $fontSize, $x, $y, $config['text'], $text);
        } else {
            // 使用TTF字体
            // 计算文字大小和位置
            $fontSize = $this->calculateFontSize($config['width'], $config['height'], $config['text']);
            $box = imagettfbbox($fontSize, 0, $fontPath, $config['text']);
            
            $textWidth = $box[2] - $box[0];
            $textHeight = $box[7] - $box[1];
            
            $x = intval(($config['width'] - $textWidth) / 2);
            $y = intval(($config['height'] - $textHeight) / 2 - $box[1]);
            // 添加文字
            imagettftext($image, $fontSize, 0, $x, $y, $text, $fontPath, $config['text']);
        }
    }
    
    /**
     * 计算合适的字体大小
     * @param int $width 图片宽度
     * @param int $height 图片高度
     * @param string $text 文字内容
     * @return int 字体大小
     */
    private function calculateFontSize($width, $height, $text)
    {
        // 获取字体路径
        $fontPath = $this->getFontPath();
        
        // 如果使用内置字体，直接返回合适的大小
        if (is_numeric($fontPath)) {
            // 根据图片尺寸选择合适的内置字体大小(1-5)
            if ($width < 100 || $height < 50) {
                return 1; // 最小字体
            } elseif ($width < 200 || $height < 100) {
                return 2;
            } elseif ($width < 400 || $height < 200) {
                return 3;
            } elseif ($width < 600 || $height < 300) {
                return 4;
            } else {
                return 5; // 最大字体
            }
        }
        
        // 使用TTF字体时计算合适大小
        $maxWidth = $width * 0.8;
        $maxHeight = $height * 0.4;
        
        // 从较大的字体开始尝试
        $fontSize = min($maxHeight, 48);
        
        // 逐渐减小字体大小直到文字适合
        while ($fontSize > 8) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = $box[2] - $box[0];
            
            if ($textWidth <= $maxWidth) {
                break;
            }
            
            $fontSize -= 2;
        }
        
        return $fontSize;
    }
    
    /**
     * 获取字体文件路径
     * @return string 字体文件路径
     */
    private function getFontPath()
    {
        // 使用系统默认字体或自定义字体
        $fontPath = ROOT_PATH . 'public/assets/addons/placeholder/font/MapleMono-NF-CN-Medium.ttf';
        
        // 如果自定义字体不存在，使用GD内置字体
        if (!file_exists($fontPath)) {
            return 5; // GD内置字体
        }
        
        return $fontPath;
    }
    
    /**
     * 应用特效
     * @param resource $image 图片资源
     * @param array $config 图片配置
     */
    private function applyEffects($image, $config)
    {
        // 应用灰度效果
        if ($config['grayscale']) {
            imagefilter($image, IMG_FILTER_GRAYSCALE);
        }
        
        // 应用模糊效果
        if ($config['blur'] > 0) {
            // 使用多次高斯模糊来实现不同程度的模糊效果
            $blurIterations = ceil($config['blur'] / 20);
            for ($i = 0; $i < $blurIterations; $i++) {
                imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
            }
        }
    }
    
    /**
     * 输出图片
     * @param resource $image 图片资源
     * @param array $config 图片配置
     */
    private function outputImage($image, $config)
    {
        // 设置Content-Type
        $mimeType = 'image/' . ($config['format'] == 'jpg' ? 'jpeg' : $config['format']);
        header('Content-Type: ' . $mimeType);
        
        // 输出图片
        switch ($config['format']) {
            case 'jpg':
                imagejpeg($image, null, 90);
                break;
            case 'png':
                imagepng($image, null, 9);
                break;
            case 'gif':
                imagegif($image);
                break;
            case 'bmp':
                imagebmp($image);
                break;
        }
    }
    
    /**
     * 将十六进制颜色转换为RGB
     * @param string $hex 十六进制颜色
     * @return array RGB颜色数组
     */
    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
    
    /**
     * 调整颜色亮度
     * @param string $hex 十六进制颜色
     * @param int $steps 调整步数
     * @return string 调整后的颜色
     */
    private function adjustBrightness($hex, $steps)
    {
        $rgb = $this->hexToRgb($hex);
        
        $r = max(0, min(255, $rgb['r'] + $steps));
        $g = max(0, min(255, $rgb['g'] + $steps));
        $b = max(0, min(255, $rgb['b'] + $steps));
        
        return sprintf('%02X%02X%02X', $r, $g, $b);
    }
}