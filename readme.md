[TOC]

## 项目名称
yuesen_air_purification - 悦森空气净化项目
## 技术架构
* PHP >7.1.0
* MySQL >5.7.0
* Nginx >1.12.0
* Laravel > 5.5.0
## 安装第三方组件
>注意：可以自定义 [`composer`](https://pkg.phpcomposer.com/) 镜像，加快拉取速度

```
$ composer update
```
## 目录结构及权限
###配置 `.env` 文件，数据库、redis 等
```
$ cp .env.example .env
```
### 创建目录
> 注意：git clone 从仓库拉取的代码，可能会存在 storage 目录缺失的问题，需要手动创建

```
$ mkdir -p storage/{app,debugbar,framework,logs}
$ mkdir -p storage/framework/{cache,sessions,testing,views}
```
### 修改权限
>注意：必须保证 `storage`，`bootstarp/cache` 有读写权限

```
$ chmod 777 -R storage bootstrap/cache
```
## 创建 KEY
```
$ php artisan key:generate
```
## 创建 storage 到 public 的软链接
```
$ php artisan storage:link
```
## 导入初始化 SQL
sql 文件位于根目录，`nt_admin_framework_init.sql`，建议使用 Navicat 导入。
