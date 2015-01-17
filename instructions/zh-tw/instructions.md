開始使用 ${app}
-----------------------------------
歡迎使用您的新 PHP 應用程式！

PHP 說明

1. [安裝 cf 指令行工具](${doc-url}/#starters/BuildingWeb.html#install_cf)。
2. [下載入門範本應用程式套件](${ace-url}/rest/apps/${app-guid}/starter-download)。
3. 解開套件並 `cd` 到該處。
4. 連接並登入 Bluemix：

		cf login -a ${api-url}

5. 部署您的應用程式：

		cf push ${app}

6. 存取您的應用程式：[${route}](//${route})

