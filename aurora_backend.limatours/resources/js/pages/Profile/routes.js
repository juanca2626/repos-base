import ProfileLayout from './Layout'
import ProfileForm from './Form'

export default [
  {
    path: 'profile',
    alias: '',
    component: ProfileLayout,
    redirect: '/profile',
    name: 'Profile',
    meta: {
      breadcrumb: 'Perfil'
    },
    children: [
      {
        path: 'edit',
        alias: '',
        component: ProfileForm,
        name: 'ProfileEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
