import './widas-cidaas-extension-client-listing-page.scss';
import template from './widas-cidaas-extension-client-listing-page.html.twig';

const { Component, Context, Data, Mixin } = Shopware;
const { Criteria } = Data;

Component.register('widas-cidaas-extension-client-listing-page', {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            isLoading: true,
            items: null,
            columns: [{
                property: 'active',
                label: this.$t('widas-cidaas-extension-client.pages.listing.columns.active'),
                allowResize: false,
                width: '50px'
            }, {
                property: 'name',
                label: this.$t('widas-cidaas-extension-client.pages.listing.columns.name'),
                routerLink: 'widas.cidaas.extension.client.edit'
            }, {
                property: 'provider',
                label: this.$t('widas-cidaas-extension-client.pages.listing.columns.provider')
            }, {
                property: 'userKeys.length',
                label: this.$t('widas-cidaas-extension-client.pages.listing.columns.users'),
                width: '100px'
            }, {
                property: 'createdAt',
                label: this.$t('widas-cidaas-extension-client.pages.listing.columns.createdAt'),
                width: '200px'
            }]
        }
    },

    created() {
        this.getList();
    },

    computed: {
        clientRepository() {
            return this.repositoryFactory.create('widas_cidaas_extension_client');
        },

        clientCriteria() {
            const result = new Criteria();
            const params = this.getListingParams();

            result.addAssociation('userKeys');
            result.setLimit(params.limit);
            result.setPage(params.page);
            result.addSorting(Criteria.sort(params.sortBy || 'name', params.sortDirection || 'ASC'));

            if (params.term && params.term.length) {
                result.addFilter(Criteria.contains('name', params.term));
            }

            return result;
        }
    },

    methods: {
        getList() {
            return this.loadData();
        },

        loadData() {
            this.isLoading = true;

            this.loadClients().then(() => {
                this.isLoading = false;
            });
        },

        loadClients() {
            this.items = null;

            return this.clientRepository
                .search(this.clientCriteria, Context.api)
                .then(items => {
                    this.items = items;
                });
        },

        getLoginColor(client) {
            if (!client.active) {
                return '#333333';
            }

            if (client.login) {
                return '#00cc00'
            }

            return '#cc0000';
        },

        getConnectColor(client) {
            if (!client.active) {
                return '#333333';
            }

            if (client.connect) {
                return '#00cc00'
            }

            return '#cc0000';
        }
    }
});
