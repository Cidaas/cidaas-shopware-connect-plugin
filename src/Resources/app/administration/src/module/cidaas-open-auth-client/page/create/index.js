import template from './cidaas-open-auth-client-create-page.html.twig';
import CidaasOpenAuthProviderApiService from "../../../../service/api/cidaas-open-auth-provider.service";

const { Component } = Shopware;

Component.register('cidaas-open-auth-client-create-page', {
    template,

    inject: [
        'CidaasOpenAuthProviderApiService',
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

            return this.CidaasOpenAuthProviderApiService
                .list()
                .then(items => {
                    this.items = items.data.map(key => ({
                        key,
                        label: this.$t(`cidaasOpenAuthClient.providers.${key}`),
                        actionLabel: this.$te(`.cidaasOpenAuthClient.providersCreate.${key}`) ?
                            this.$t(`cidaasOpenAuthClient.providersCreate.${key}`) :
                            this.$t('cidaas-open-auth-client.pages.create.actions.create'),
                        classes: [
                            'cidaas-open-auth-client-create-page-providers-provider',
                            `cidaas-open-auth-client-create-page-providers--provider-${key}`,
                        ],
                    }))
                        .sort((a, b) =>
                            a.label.localeCompare(b.label)
                        );
                    this.isLoading = false;
                });
        },

        createClient(provider) {
            return this.CidaasOpenAuthProviderApiService
                .factorize(provider.key)
                .then(response => {
                    this.$router.push({ name: 'cidaas.open.auth.client.edit', params: { id: response.data.id } });
                });
        }
    }
});
