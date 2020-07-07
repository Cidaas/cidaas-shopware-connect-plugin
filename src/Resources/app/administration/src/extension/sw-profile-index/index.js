import template from './sw-profile-index.html.twig';

const { Component, Context, Data } = Shopware;
const { Criteria } = Data;

Component.override('sw-profile-index', {
    template,

    data() {
        return {
            widasCidaasExtensionLoading: true,
            widasCidaasExtensionClients: [],
        }
    },

    computed: {
        widasCidaasExtensionClientsRepository() {
            return this.repositoryFactory.create('widas_cidaas_extension_client')
        },

        widasCidaasExtensionUserEmailsRepository() {
            return this.repositoryFactory.create('widas_cidaas_extension_user_email')
        },

        widasCidaasExtensionUserKeysRepository() {
            return this.repositoryFactory.create('widas_cidaas_extension_user_key')
        },

        widasCidaasExtensionUserTokensRepository() {
            return this.repositoryFactory.create('widas_cidaas_extension_user_token')
        }
    },

    methods: {
        loadWidasCidaasExtension(userId) {
            this.widasCidaasExtensionLoading = true;

            this.widasCidaasExtensionClients = [];
            const criteria = new Criteria();
            criteria.getAssociation('userKeys').addFilter(Criteria.equals('userId', userId));
            criteria.getAssociation('userEmails').addFilter(Criteria.equals('userId', userId));
            criteria.getAssociation('userTokens').addFilter(Criteria.equals('userId', userId));

            return this.widasCidaasExtensionClientsRepository
                .search(criteria, Context.api)
                .then(result => {
                    this.widasCidaasExtensionClients = result.filter(client =>
                        (client.active && client.connect) || client.userKeys.length > 0
                    );
                    this.widasCidaasExtensionLoading = false;
                });
        },

        revokeWidasCidaasExtensionUserKey(item) {
            return Promise.all([
                ...item.userKeys.map(key =>
                    this.widasCidaasExtensionUserKeysRepository.delete(key.id, Context.api),
                ),
                ...item.userEmails.map(email =>
                    this.widasCidaasExtensionUserEmailsRepository.delete(email.id, Context.api)
                ),
                ...item.userTokens.map(token =>
                    this.widasCidaasExtensionUserTokensRepository.delete(token.id, Context.api)
                )
            ])
                .then(() => this.loadWidasCidaasExtension(this.user.id));
        },

        redirectToLoginMask(clientId) {
            this.widasCidaasExtensionClientsRepository
                .httpClient
                .get(`/_admin/open-auth/${clientId}/connect`)
                .then(response => {
                    window.location.href = response.data.target;
                });
        },

        getUserData() {
            return this.$super('getUserData').then(user => {
                return this.loadWidasCidaasExtension(user.id).then(() => user);
            })
        }
    }
});
