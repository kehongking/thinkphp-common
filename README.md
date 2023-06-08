# thinkphp公共扩展包
### 1.执行命令安装扩展包
```
composer require kehongking/thinkphp-common
```
### 2.app\middleware.php 中定义请求日志类
##### \KeHongKing\ThinkphpCommon\RequestLog::class,

### 3.app\middleware.php 中定义跨域类
##### \KeHongKing\ThinkphpCommon\CrossDomain::class,

### 4.app\provider.php 中定义异常处理类
##### 'think\exception\Handle' => '\\KeHongKing\\ThinkphpCommon\\HandleException',


