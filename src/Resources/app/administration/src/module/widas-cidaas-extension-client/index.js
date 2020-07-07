import './page/create';
import './page/edit';
import './page/listing';
import snippets from './snippets';

const { Module } = Shopware;

Module.register('widas-cidaas-extension-client', {
    type: 'plugin',
    name: 'widas-cidaas-extension-client.module.name',
    title: 'widas-cidaas-extension-client.module.title',
    description: 'widas-cidaas-extension-client.module.description',
    color: '#FFC2A2',
    icon: 'default-action-log-in',
    snippets,

    routes: {
        create: {
            component: 'widas-cidaas-extension-client-create-page',
            path: 'create',
            meta: {
                parentPath: 'widas.cidaas.extension.client.settings'
            },
        },
        edit: {
            component: 'widas-cidaas-extension-client-edit-page',
            path: 'edit/:id',
            meta: {
                parentPath: 'widas.cidaas.extension.client.settings'
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
            component: 'widas-cidaas-extension-client-listing-page',
            path: 'settings',
            meta: {
                parentPath: 'sw.settings.index'
            }
        },
    },

    settingsItem: [{
        to: 'widas.cidaas.extension.client.settings',
        group: 'system',
        icon: 'default-action-log-in',
    }]
});
