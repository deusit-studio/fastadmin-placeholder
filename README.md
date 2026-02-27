

# FastAdmin 占位图片生成插件

一个类似 via.placeholder.com 的占位图片生成插件，支持自定义尺寸、颜色、文字、格式、灰度、模糊等多种功能。

[测试站点](https://via.itnan.ren) | [Gitee 仓库](https://gitee.com/deusit/fastadmin-placeholder) | [GitHub 仓库](https://github.com/deusit-studio/fastadmin-placeholder)

## 功能特点

- ✅ **尺寸定制**：支持自定义图片宽度和高度
- ✅ **颜色定制**：支持背景色和文字颜色设置
- ✅ **文字定制**：支持自定义占位文字内容
- ✅ **格式支持**：支持 JPG、PNG、GIF、BMP 等常见图片格式
- ✅ **特效选项**：灰度、模糊、等比缩放等图片效果
- ✅ **批量生成**：一次配置生成多种规格图片
- ✅ **图片预览**：实时预览生成效果
- ✅ **历史记录**：保存最近使用的配置
- ✅ **多种复制格式**：支持直接链接、Markdown、HTML、BBCode 格式

## 系统要求

- FastAdmin >= 1.0.0
- PHP >= 7.0
- GD 库扩展

## 安装方法

### 方法一：通过 FastAdmin 插件市场安装（推荐）

1. 登录 FastAdmin 后台
2. 进入"插件管理" -> "在线安装"
3. 搜索"占位图片生成器"或"placeholder"
4. 点击"安装"按钮

### 方法二：手动安装

1. 下载插件源码
2. 将插件解压到 FastAdmin 的 `addons` 目录下，确保目录名为 `placeholder`
3. 登录 FastAdmin 后台，进入"插件管理"
4. 找到"占位图片生成器"插件，点击"安装"按钮

## 目录结构

```
placeholder/
├── assets/                    # 前端资源文件
│   ├── font/                  # 字体文件
│   └── libs/layui/            # Layui UI 库
├── controller/                # 控制器
│   └── Index.php              # 主控制器
├── library/                   # 核心类库
│   └── Placeholder.php        # 占位图生成类
├── view/                     # 视图文件
│   └── index/
│       └── index.html         # 前端页面
├── Placeholder.php           # 插件主类
├── config.php                # 配置文件
├── config.html               # 配置页面
├── info.ini                  # 插件信息
└── composer.json              # Composer 配置
```

## 使用方法

### 后台界面使用

1. 登录 FastAdmin 后台
2. 点击左侧菜单"插件" -> "占位图片生成器"
3. 在配置面板中设置所需的参数：
   - **尺寸**：设置图片宽度和高度
   - **背景颜色**：选择背景颜色（支持十六进制颜色值）
   - **文字颜色**：选择文字颜色
   - **自定义文字**：输入想要显示的文字
   - **图片格式**：选择输出格式（JPG、PNG、GIF、BMP）
   - **灰度效果**：开启灰度效果
   - **模糊效果**：设置模糊程度（0-100）
   - **远程图片**：可设置远程图片作为背景
4. 点击"生成图片"按钮查看效果
5. 复制生成的图片链接使用

### 直接 URL 访问

插件支持通过 URL 直接访问生成图片，格式如下：

```
http://yourdomain.com/addons/placeholder/index/image/[width]x[height].[format]?[options]
```

**参数说明：**

| 参数 | 说明 | 默认值 |
|------|------|--------|
| `width` | 图片宽度 | 300 |
| `height` | 图片高度 | 200 |
| `format` | 图片格式 (jpg/png/gif/bmp) | png |
| `bgcolor` | 背景颜色（十六进制） | CCCCCC |
| `textcolor` | 文字颜色（十六进制） | 666666 |
| `text` | 自定义文字 | 显示尺寸 |
| `grayscale` | 是否灰度 (1/0) | 0 |
| `blur` | 模糊程度 (0-100) | 0 |
| `image_url` | 远程图片地址 | - |

**使用示例：**

```bash
# 基础用法
http://yourdomain.com/addons/placeholder/index/image/300x200.png

# 自定义颜色和文字
http://yourdomain.com/addons/placeholder/index/image/600x400.jpg?bgcolor=FF0000&textcolor=FFFFFF&text=Hello

# 灰度效果 + 模糊
http://yourdomain.com/addons/placeholder/index/image/800x600.png?grayscale=1&blur=20

# 远程图片背景
http://yourdomain.com/addons/placeholder/index/image/800x600.png?image_url=https://example.com/image.png
```

### API 调用

可以在代码中直接调用插件的 API 生成图片：

```php
// 引入 Placeholder 类
use addons\placeholder\library\Placeholder;

// 创建实例
$placeholder = new Placeholder();

// 生成图片
$result = $placeholder->generate([
    'width'      => 300,
    'height'     => 200,
    'bgcolor'    => 'CCCCCC',
    'textcolor'  => '666666',
    'text'       => '测试图片',
    'format'     => 'png',
    'grayscale'  => false,
    'blur'       => 0
]);

// 输出图片
$result->output();
```

## 伪静态配置

若支持无参数文件名后缀，需添加伪静态规则。

### 方式一：仅针对占位图路径

```nginx
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

### 方式二：全局图片路径（所有 .png/.jpg 等）

```nginx
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

1. **GD 库要求**：确保服务器已安装 GD 库扩展，可通过 `phpinfo()` 查看
2. **性能优化**：对于高流量网站，建议配置缓存以提高性能
3. **资源限制**：生成大尺寸图片可能会消耗较多服务器资源，请合理设置最大尺寸限制
4. **字体支持**：插件内置了默认字体，支持中文显示

## 常见问题

### Q: 生成的图片不显示怎么办？

A: 请检查服务器是否已安装 GD 库扩展，可通过 `phpinfo()` 查看。如果未安装，请联系服务器管理员安装 GD 扩展。

### Q: 如何修改默认配置？

A: 可以修改 `config.php` 文件中的配置项，或在 FastAdmin 后台插件配置中修改。

### Q: 支持哪些图片格式？

A: 默认支持 JPG、PNG、GIF、BMP 四种格式，可在配置文件中修改支持格式列表。

### Q: 如何实现批量生成？

A: 在后台界面中，可以使用批量生成功能，一次配置生成多种规格的图片。

## 更新日志

### v1.0.0 (2026-02-27)

- ✅ 初始版本发布
- ✅ 支持基本的占位图片生成功能
- ✅ 支持自定义尺寸、颜色、文字、格式等
- ✅ 支持灰度、模糊等特效
- ✅ 支持批量生成和历史记录

## 许可证

本插件基于 [Apache-2.0 许可证](LICENSE) 开源。

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个插件！

## 特别鸣谢

Fastadmin：https://www.fastadmin.net/

ThinkPHP：https://www.thinkphp.cn

jQuery：https://jquery.com

Layui: https://layui.dev/

## 联系方式

如有问题或建议，请通过以下方式联系：

- 📧 Email: 505097558@qq.com
- 🐞 Gitee: https://gitee.com/deusit/fastadmin-placeholder
- 🐙 GitHub: https://github.com/deusit-studio/fastadmin-placeholder