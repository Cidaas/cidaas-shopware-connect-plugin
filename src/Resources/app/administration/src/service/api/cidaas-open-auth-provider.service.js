const { Classes } = Shopware;
const { ApiService } = Classes;

class CidaasOpenAuthProviderApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'cidaas_open_auth_provider') {
        super(httpClient, loginService, apiEndpoint);
    }

    list() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(`_action/${this.getApiBasePath()}/list`, { headers })
            .then(response => ApiService.handleResponse(response));
    }

    factorize(providerKey) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/factorize`, { provider_key: providerKey }, { headers })
            .then(response => ApiService.handleResponse(response));
    }
}

export default CidaasOpenAuthProviderApiService;
