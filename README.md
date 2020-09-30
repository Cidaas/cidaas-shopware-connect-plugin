## Plugin For cidaas oauth2 Connect in Shopware System

**Note: This plugins only works with the Shopware Platform** (see: https://github.com/shopware/development)
## Setup

- Move plugin to custom/plugins
- Run following commands to activate the plugin

```bash
bin/console plugin:update
bin/console plugin:install --activate CidaasOauthConnect
bin/console cache:clear
```

## Installation Guide:
1. Install & activate the plugin in Shopware Admin > settings > System > Plugins
2. In the plugin section of Shopware Admin, click the three dots next to the plugin and navigate to Plugin Config page
3. In order to fill in the details of the cidaas Plugin Config page, some configuration must be done in the cidaas Admin Dashboard. Navigate to the cidaas Admin Dashboard of your cidaas instance. Create a new app (OAuth2 Client) for your Shopware Store. How to do this we show here: https://docs.cidaas.de/manage-applications/app-settings.html
4. Now transfer the fields from the newly created app (e.g. Client ID) to the corresponding field on the cidaas Shopware Plugin Config page.
5. The remaining fields that need to be filled out on the cidaas Shopware Plugin Config page, like the Authz URL, can be found in the Endpoints tab of the cidaas Admin Dashboard.(https://docs.cidaas.de/endpoints/oauth2-endpoints.html)

Further details and a complete configuration guide can be found in our developer documentation: https://docs.cidaas.de/extension/shopware_plugin.html

To learn more on what features of cidaas you can configure using the cidaas shopware plugin, please see: https://www.cidaas.com/shopware/