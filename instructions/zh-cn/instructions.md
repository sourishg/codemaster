${app} 入门
-----------------------------------
欢迎使用新 PHP 应用程序！
PHP 描述

1. [安装 cf 命令行工具](${doc-url}/#starters/BuildingWeb.html#install_cf)。
2. [下载起动器应用程序包](${ace-url}/rest/apps/${app-guid}/starter-download)。
3. 将程序包和 `cd` 抽取到它。
4. 连接并登录 Bluemix：

		cf login -a ${api-url}

5. 部署您的应用程序：

		cf push ${app}

6. 访问您的应用程序：[${route}](//${route})

