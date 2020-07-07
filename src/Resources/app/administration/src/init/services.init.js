import WidasCidaasExtensionProviderApiService from '../service/api/widas-cidaas-extension-provider.service';

const { Application } = Shopware;

Application.addServiceProvider('WidasCidaasExtensionProviderApiService', container => {
    const initContainer = Application.getContainer('init');
    return new WidasCidaasExtensionProviderApiService(initContainer.httpClient, container.loginService);
});
