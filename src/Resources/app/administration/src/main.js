import './app/components/widas-cidaas-extension-scope-field';
import './extension/sw-profile-index';
import './extension/sw-settings-index';
import './module/widas-cidaas-extension-client';
import './provider/cidaas/widas-cidaas-extension-client-edit-page';
import './init/services.init';
import globalSnippets from './snippets';
import extensionSnippets from './extension/snippets';
import providerCidaasSnippets from './provider/cidaas/snippets';

const { Locale } = Shopware;

[globalSnippets, extensionSnippets, providerCidaasSnippets]
    .map(Object.entries)
    .flat()
    .forEach(([lang, snippets]) => Locale.extend(lang, snippets));
