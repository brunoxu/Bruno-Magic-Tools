Bruno-Magic-Tools
=================

Bruno Magic Tools是用来添加附加的功能来增强当前主题，避免升级主题过程中的丢失。

Bruno Magic Tools本质上是一个插件，用来存放针对主题的各种样式的修改，也可以用来添加小功能，或是针对插件、WordPress本身功能的一些修改。

Bruno Magic Tools项目
---------

Bruno Magic Tools是一个基于GPLv2证书的开源软件，可以自由使用、修改和传播。

项目起源于针对www.brunoxu.com网站 + travelify主题的修改，源码内容部分内容是只对www.brunoxu.com网站适用，大部分功能代码可以直接复制使用，或者做很小的修改。每个功能代码段有注释说明具体作用。

Bruno Magic Tools功能列表
---------

功能都是一个个的代码段，有注释开头和注释结束标明，可以加上代码段的控制判断来控制是否加载。

* travelify主题的列表页post文字数量修改正常
* travelify主题的文章页相关列表，此段可以仅是样式不通用，功能是通用的，可以修改样式后用于其他地方。还有源码中的除bruno-magic-tools.php主文件外，其他文件和文件夹都是此功能需要的问题，如果不需要这个功能，可以一并删除。
* travelify主题的右侧部分内容跟随滚动，可以少量修改后用于其他主题。
* 所有主题适用，文章列表标题添加new图标
* 通用功能，debug in out buffering
* 通用功能，百度分享JS，设置了文字和图片数据，保证分享到QQ空间的信息正确。
* travelify主题： 迟加载效果优化，图片宽度100%，高度auto时，动态计算出图片实时高度，保证图位不变形。
* Remove Google Fonts References插件： 开启后可以让Remove Google Fonts References插件的处理先于Useso take over Google插件的处理
* 通用功能，Prism syntax highlighter代码高亮实现
* 不断添加中。。。
