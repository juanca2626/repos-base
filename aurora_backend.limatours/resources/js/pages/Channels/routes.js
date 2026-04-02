import ChannelsLayout from './Layout'
import ChannelsList from './List'
import ChannelsForm from './Form'

import UserLayout from './User/Layout'
import UserList from './User/List'

import ChannelLogsLayout from './Logs/Layout'
import ChannelLogsList from './Logs/List'
import ChannelLogShow from './Logs/Show'

export default [
  {
    path: 'channels',
    alias: '',
    component: ChannelsLayout,
    redirect: '/channels/list',
    name: 'Channels',
    meta: {
      breadcrumb: 'Channel'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ChannelsList,
        name: 'ChannelsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ChannelsForm,
        name: 'ChannelsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ChannelsForm,
        name: 'ChannelsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: ':channel_id/users',
        alias: '',
        component: UserLayout,
        redirect: ':channel_id/users/list',
        name: 'UserLayout',
        meta: {
          breadcrumb: 'Users'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: UserList,
            name: 'UserList',
            meta: {
              breadcrumb: 'Lista'
            }
          }
        ]
      },
      {
        path: ':channel_id/logs',
        alias: '',
        component: ChannelLogsLayout,
        redirect: ':channel_id/logs/list',
        name: 'ChannelLogsLayout',
        meta: {
          breadcrumb: ':channel_id/logs/list'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: ChannelLogsList,
            name: 'ChannelLogsList',
            meta: {
              breadcrumb: 'Lista'
            }
          },
          {
            path: ':channel_id/logs/:id',
            alias: 'logList',
            component: ChannelLogShow,
            name: 'ChannelLogShow',
            meta: {
              breadcrumb: 'Log'
            }
          }
        ]
      }
    ]
  }]
