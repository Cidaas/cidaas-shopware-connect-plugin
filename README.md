## Plugin For cidaas oauth2 Connect in Shopware System

**Note: This plugins only works with the Shopware Platform** (see: https://github.com/shopware/development)

### Setup:

- Move plugin to custom/plugins
- Run bin/console plugin:update
- Run bin/console plugin:install --activate CidaasOpenAuth
- Run bin/console cache:clear

### Create Your Client In Cidaas

- Goto your cidaas instance and Create new Single Page webapplication

### Adding Base Url in Provider Configuration

- Now goto src -> Component -> OpenAuth -> Widas.php

```php
public function getBaseUrl() {
    return '<Add your tenant base Url Here (https://example.cidaas.de)>';
}
```

### Configuring in Shopware Admin UI

- Login in to your shopware admin UI and goto Settings -> System -> OauthLogin menu
- When you client that menu it'll show list page. In that Page you can create new Client Configuration by just pressing Create button