import './page/create';
import './page/edit';
import './page/listing';
import snippets from './snippets';

const { Module } = Shopware;

Module.register('cidaas-open-auth-client', {
    type: 'plugin',
    name: 'cidaas-open-auth-client.module.name',
    title: 'cidaas-open-auth-client.module.title',
    description: 'cidaas-open-auth-client.module.description',
    color: '#FFC2A2',
    icon: 'default-action-log-in',
    snippets,

    routes: {
        create: {
            component: 'cidaas-open-auth-client-create-page',
            path: 'create',
            meta: {
                parentPath: 'cidaas.open.auth.client.settings'
            },
        },
        edit: {
            component: 'cidaas-open-auth-client-edit-page',
            path: 'edit/:id',
            meta: {
                parentPath: 'cidaas.open.auth.client.settings'
            },
            props: {
                default(route) {
                    return {
                        clientId: route.params.id,
                    };
                }
            }
        },
        settings: {
            component: 'cidaas-open-auth-client-listing-page',
            path: 'settings',
            meta: {
                parentPath: 'sw.settings.index'
            }
        },
    },

    settingsItem: [{
        to: 'cidaas.open.auth.client.settings',
        group: 'system',
        icon: 'default-action-log-in',
    }]
});
