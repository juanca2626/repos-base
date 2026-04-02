import FrequentQuestionsLayout from './Layout'
import FrequentQuestionsList from './List'
import FrequentQuestionsForm from './Form'

export default [
    {
        path: 'frequent_questions',
        component: FrequentQuestionsLayout,
        redirect: {
            name: 'FrequentQuestionsList'
        },
        name: 'frequentQuestions',
        meta: {
            breadcrumb: 'Preguntas frecuentes'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: FrequentQuestionsList,
                name: 'FrequentQuestionsList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: FrequentQuestionsForm,
                name: 'FrequentQuestionsAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: FrequentQuestionsForm,
                name: 'FrequentQuestionsEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
    }]
