${app}: 始めに
-----------------------------------
新しい PHP アプリへようこそ

PHP の説明

1. [cf コマンド・ライン・ツールをインストールします](${doc-url}/#starters/BuildingWeb.html#install_cf)。
2. [スターター・アプリケーション・パッケージをダウンロードします](${ace-url}/rest/apps/${app-guid}/starter-download)。
3. パッケージを解凍し、そのパッケージに `cd` で移動します。
4. Bluemix への接続およびログイン:

		cf login -a ${api-url}

5. アプリのデプロイ:

		cf push ${app}

6. アプリへのアクセス: [${route}](//${route})

