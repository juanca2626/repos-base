import MealsLayout from './Layout'
import MealsList from './List'
import MealsForm from './Form'

export default [
  {
    path: 'meals',
    alias: '',
    component: MealsLayout,
    redirect: '/meals/list',
    name: 'Meals',
    meta: {
      breadcrumb: 'Meals'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: MealsList,
        name: 'MealsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: MealsForm,
        name: 'MealsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: MealsForm,
        name: 'MealsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
