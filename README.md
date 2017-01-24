# 插件市场 (Unofficial)

## 为什么是“非官方”？
也许插件市场这个东西应该由 [Blessing Skin Server](https://github.com/printempw/blessing-skin-server) 的作者来做，但毕竟他确实很忙（尽管这样他还是抽空为大家解决皮肤站的问题），并且自己也算是学点东西，于是花了点时间做了这个插件市场。也算是对  [Blessing Skin Server](https://github.com/printempw/blessing-skin-server) 的一份贡献吧。

我并没有采用 Fork 原项目，然后修改，接着 Pull Request 的方法，而是以插件的形式做了出来。一时当时考虑到以后可能会有官方的插件市场，如果直接修改代码，可能会有冲突；二是那时候插件系统刚刚出炉，也顺便体验一把开发插件的过程。

## 下载
您可以在这个项目的 releases 页面找到最新版。插件市场上也有这个插件。（那当然了）
另外，在 Blessing Skin Server 的正式版压缩包中也自带了这个插件（可能不是最新的），不过默认是处于禁用状态，您需要通过“插件管理”来启用它。

## 安装
与其它的插件的安装方法相同，您只需要把本插件的压缩包解压在皮肤站里的 `plugins` 目录里，皮肤站的插件系统将自动读取信息**（但不一定会启用它！）**。为了方便以后插件市场的更新和升级，请确保它的目录名为 `unofficial-plugins-market` （如果您从 GitHub 的 releases 页面下载这个插件，那么它的目录名很有可能不是这个）。

## 一般使用
### 插件市场源
默认情况下，本插件已经设置好一个[插件市场源](https://plugin.sealfu.cf/)，目前由我维护。您可以通过“插件配置”页面来使用其它您喜欢的插件市场源，如果您想在“插件配置”页中的“常用源列表”中展示您搭建的源，请在**认真**阅读“如果您想搭建插件市场”这一部分并确保你的源能正常工作后，通过 Issue 告诉我。

### 多版本支持
> 注：该功能需要插件市场服务端的支持

如果您细心观察的话，您可以发现在市场中的插件列表内，“版本”这一栏是一个下拉框，这意味着您可以下载某个插件的以前的版本。

### 替换默认
如果您觉得使用此插件之后，皮肤站的左侧栏多了一项菜单项，影响美观，您可以在“插件配置”页面中打开“接管系统默认的插件市场”这一选项。

### 更新提醒
新版本的插件往往是修复了旧版本的错误和提供了新功能。为了您第一时间能收到更新，此插件还提供“更新提醒”的功能。开启此功能后，如果插件有新版本发布，您可以在皮肤站的左侧栏看到标有可更新的插件数量的图标。
#### 不提醒
不对任何插件的新版本作提醒。（不推荐）
#### 仅正式版
某些插件的更新可能还处于预览、测试阶段而不稳定，此选项可以让您只收到正式版的更新提醒。（推荐，尤其是生产环境）
#### 全部提醒
对于预览版、测试版，像正式版那样同样收到更新提醒，并且预览版会以颜色不同的图标出现。适合喜欢尝鲜的用户。

### 自动启用
为了您拥有更顺畅的插件安装体验，此插件可以让您在完成安装插件后，自动启用插件。默认情况下这项功能处于关闭状态，您需要在“插件配置”页面启用它。