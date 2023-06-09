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



### 5.config\middleware.php 中定义中间件
    // 别名或分组
    'alias'    => [
        'jwt' => \KeHongKing\ThinkphpCommon\Jwt::class,
        'auth' => \KeHongKing\ThinkphpCommon\Auth::class,
    ],


