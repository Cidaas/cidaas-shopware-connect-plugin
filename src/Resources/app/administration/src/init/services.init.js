import CidaasOpenAuthProviderApiService from '../service/api/cidaas-open-auth-provider.service';

const { Application } = Shopware;

Application.addServiceProvider('CidaasOpenAuthProviderApiService', container => {
    const initContainer = Application.getContainer('init');
    return new CidaasOpenAuthProviderApiService(initContainer.httpClient, container.loginService);
});
