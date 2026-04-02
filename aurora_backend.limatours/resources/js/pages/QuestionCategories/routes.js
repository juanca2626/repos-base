import QuestionCategoriesLayout from './Layout'
import QuestionCategoriesList from './List'
import QuestionCategoriesForm from './Form'

export default [
    {
        path: 'question_categories',
        component: QuestionCategoriesLayout,
        redirect: {
            name: 'QuestionCategoriesList'
        },
        name: 'QuestionCategories',
        meta: {
            breadcrumb: 'Categorias de Preguntas'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: QuestionCategoriesList,
                name: 'QuestionCategoriesList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: QuestionCategoriesForm,
                name: 'QuestionCategoriesAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: QuestionCategoriesForm,
                name: 'QuestionCategoriesEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
    }]
