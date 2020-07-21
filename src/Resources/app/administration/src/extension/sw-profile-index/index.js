import template from './sw-profile-index.html.twig';

const { Component, Context, Data } = Shopware;
const { Criteria } = Data;

Component.override('sw-profile-index', {
    template,

    data() {
        return {
            cidaasOpenAuthLoading: true,
            cidaasOpenAuthClients: [],
        }
    },

    computed: {
        cidaasOpenAuthClientsRepository() {
            return this.repositoryFactory.create('cidaas_open_auth_client')
        },

        cidaasOpenAuthUserEmailsRepository() {
            return this.repositoryFactory.create('cidaas_open_auth_user_email')
        },

        cidaasOpenAuthUserKeysRepository() {
            return this.repositoryFactory.create('cidaas_open_auth_user_key')
        },

        cidaasOpenAuthUserTokensRepository() {
            return this.repositoryFactory.create('cidaas_open_auth_user_token')
        }
    },

    methods: {
        loadCidaasOpenAuth(userId) {
            this.cidaasOpenAuthLoading = true;

            this.cidaasOpenAuthClients = [];
            const criteria = new Criteria();
            criteria.getAssociation('userKeys').addFilter(Criteria.equals('userId', userId));
            criteria.getAssociation('userEmails').addFilter(Criteria.equals('userId', userId));
            criteria.getAssociation('userTokens').addFilter(Criteria.equals('userId', userId));

            return this.cidaasOpenAuthClientsRepository
                .search(criteria, Context.api)
                .then(result => {
                    this.cidaasOpenAuthClients = result.filter(client =>
                        (client.active && client.connect) || client.userKeys.length > 0
                    );
                    this.cidaasOpenAuthLoading = false;
                });
        },

        revokeCidaasOpenAuthUserKey(item) {
            return Promise.all([
                ...item.userKeys.map(key =>
                    this.cidaasOpenAuthUserKeysRepository.delete(key.id, Context.api),
                ),
                ...item.userEmails.map(email =>
                    this.cidaasOpenAuthUserEmailsRepository.delete(email.id, Context.api)
                ),
                ...item.userTokens.map(token =>
                    this.cidaasOpenAuthUserTokensRepository.delete(token.id, Context.api)
                )
            ])
                .then(() => this.loadCidaasOpenAuth(this.user.id));
        },

        redirectToLoginMask(clientId) {
            this.cidaasOpenAuthClientsRepository
                .httpClient
                .get(`/_admin/open-auth/${clientId}/connect`)
                .then(response => {
                    window.location.href = response.data.target;
                });
        },

        getUserData() {
            return this.$super('getUserData').then(user => {
                return this.loadCidaasOpenAuth(user.id).then(() => user);
            })
        }
    }
});
