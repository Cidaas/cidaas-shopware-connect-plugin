import template from './widas-cidaas-extension-client-create-page.html.twig';
import WidasCidaasExtensionProviderApiService from "../../../../service/api/widas-cidaas-extension-provider.service";

const { Component } = Shopware;

Component.register('widas-cidaas-extension-client-create-page', {
    template,

    inject: [
        'WidasCidaasExtensionProviderApiService',
    ],

    data() {
        return {
            isLoading: true,
            items: null
        }
    },

    created() {
        this.loadData();
    },

    methods: {
        loadData() {
            this.isLoading = true;

            this.loadProviders().then(() => {
                this.isLoading = false;
            });
        },

        loadProviders() {
            this.items = [];

            return this.WidasCidaasExtensionProviderApiService
                .list()
                .then(items => {
                    this.items = items.data.map(key => ({
                        key,
                        label: this.$t(`widasCidaasExtensionClient.providers.${key}`),
                        actionLabel: this.$te(`.widasCidaasExtensionClient.providersCreate.${key}`) ?
                            this.$t(`widasCidaasExtensionClient.providersCreate.${key}`) :
                            this.$t('widas-cidaas-extension-client.pages.create.actions.create'),
                        classes: [
                            'widas-cidaas-extension-client-create-page-providers-provider',
                            `widas-cidaas-extension-client-create-page-providers--provider-${key}`,
                        ],
                    }))
                        .sort((a, b) =>
                            a.label.localeCompare(b.label)
                        );
                    this.isLoading = false;
                });
        },

        createClient(provider) {
            return this.WidasCidaasExtensionProviderApiService
                .factorize(provider.key)
                .then(response => {
                    this.$router.push({ name: 'widas.cidaas.extension.client.edit', params: { id: response.data.id } });
                });
        }
    }
});
