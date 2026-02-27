# FastAdmin占位图片生成插件

一个类似via.placeholder.com的占位图片生成插件，支持自定义尺寸、颜色、文字、格式、灰度、模糊等多种功能。
[测试站点](https://via.itnan.ren)

## 功能特点

- ✅ **尺寸定制**：支持自定义图片宽度和高度
- ✅ **颜色定制**：支持背景色和文字颜色设置
- ✅ **文字定制**：支持自定义占位文字内容
- ✅ **格式支持**：支持jpg、png、gif等常见图片格式
- ✅ **特效选项**：灰度、模糊、等比缩放等图片效果
- ✅ **批量生成**：一次配置生成多种规格图片
- ✅ **图片预览**：实时预览生成效果
- ✅ **历史记录**：保存最近使用的配置
- ✅ **多种复制格式**：支持直接链接、Markdown、HTML、BBCode格式

## 安装方法

### 方法一：通过FastAdmin插件市场安装（推荐）

1. 登录FastAdmin后台
2. 进入"插件管理" -> "在线安装"
3. 搜索"占位图片生成器"或"placeholder"
4. 点击"安装"按钮

### 方法二：手动安装

1. 下载插件源码
2. 将插件解压到FastAdmin的`addons`目录下，确保目录名为`placeholder`
3. 登录FastAdmin后台，进入"插件管理"
4. 找到"占位图片生成器"插件，点击"安装"按钮

## 使用方法

### 后台界面使用

1. 登录FastAdmin后台
2. 点击左侧菜单"插件" -> "占位图片生成器"
3. 在配置面板中设置所需的参数
4. 点击"生成图片"按钮查看效果
5. 复制生成的图片链接使用

### 直接URL访问

插件支持通过URL直接访问生成图片，格式如下：

```
http://yourdomain.com/addons/placeholder/index/image/[width]x[height].[format]?[options]
```

参数说明：
- `width`：图片宽度，默认300
- `height`：图片高度，默认200
- `format`：图片格式，可选jpg、png、gif，默认png
- `bgcolor`：背景颜色，十六进制，默认CCCCCC
- `textcolor`：文字颜色，十六进制，默认666666
- `text`：自定义文字，默认显示尺寸
- `grayscale`：是否灰度，1/0，默认0
- `blur`：模糊程度，0-100，默认0
- `image_url`：远程图片地址

示例：
- `http://yourdomain.com/addons/placeholder/index/image/300x200.png`(需添加伪静态支持)
- `http://yourdomain.com/addons/placeholder/index/image/600x400.jpg?bgcolor=FF0000&textcolor=FFFFFF&text=Hello`
- `http://yourdomain.com/addons/placeholder/index/image/800x600.png?grayscale=1&blur=20&image_url=https://via.itnan.ren/assets/img/avatar.png`

### API调用

可以在代码中直接调用插件的API生成图片：

```php
// 引入Placeholder类
use addons\placeholder\library\Placeholder;

// 创建实例
$placeholder = new Placeholder();

// 生成图片
$placeholder->generate([
    'width' => 300,
    'height' => 200,
    'bgcolor' => 'CCCCCC',
    'textcolor' => '666666',
    'text' => '测试图片',
    'format' => 'png',
    'grayscale' => false,
    'blur' => 0
]);
```

## 配置说明

若支持无参数文件名后缀，需添加伪静态
placeholder/image 为插件中伪静态设置

```
location ~ ^/placeholder/image/.+\.(gif|jpg|jpeg|png|bmp)$ {
    if (-f $request_filename) {
        expires      30d;
        break;
    }
    rewrite ^/placeholder/image/(.*)$ /index.php?s=/addons/placeholder/index/image/size/$1 last;
    access_log off;
    error_log off;
}
```

若想彻底设置所有图片路径的伪静态的话 (http://yourdomain.com/300x200.png?...)

```
location ~ \.(gif|jpg|jpeg|png|bmp)$ {
    if (-f $request_filename) {
        expires      30d;
        break;
    }
    rewrite ^/(.*)$ /index.php?s=/addons/placeholder/index/image/size/$1 last;
    access_log off;
    error_log off;
}
```

## 注意事项

1. 确保服务器已安装GD库扩展
2. 对于高流量网站，建议配置缓存以提高性能
3. 生成大尺寸图片可能会消耗较多服务器资源，请合理设置最大尺寸限制

## 常见问题

### Q: 生成的图片不显示怎么办？
A: 请检查服务器是否已安装GD库扩展，可通过`phpinfo()`查看。

### Q: 如何修改默认配置？
A: 可以修改`config.php`文件中的配置项，或在FastAdmin后台插件配置中修改。

### Q: 支持哪些图片格式？
A: 默认支持jpg、png、gif三种格式，可在配置中修改。

## 更新日志

### v1.0.0 (2026-02-27)
- 初始版本发布
- 支持基本的占位图片生成功能
- 支持自定义尺寸、颜色、文字、格式等
- 支持灰度、模糊等特效
- 支持批量生成和历史记录

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个插件！

## 联系方式

如有问题或建议，请通过以下方式联系：
- Email: 505097558@qq.com
- Gitee: https://gitee.com/deusit/fastadmin-placeholder
- GitHub: https://github.com/deusit-studio/fastadmin-placeholder