# 服务监控扩展包
### 1.在composer.json下repositories下新增
```
{
    "type": "vcs",
    "url": "http://git.shall-buy.top/pkg/service-monitor.git"
}
```
### 2.执行命令安装扩展包
##### composer require shall-buy/service-monitor --版本
### 4.php artisan vendor:publish --provider="ShallBuy\ServiceMonitor\serviceMonitorProvider" 发布配置文件
### 5.php artisan serviceMonitor:init  运行此命令发送服务启动通知
### 6.使用方法
#### $factory = ServiceMonitorFactory::getInstance();
| 方法    |备注     |
|---|---|
| $factory->monitorNotify();  |服务启动通知 |
| $factory->dingTalkNotify();  |钉钉预警通知 |

