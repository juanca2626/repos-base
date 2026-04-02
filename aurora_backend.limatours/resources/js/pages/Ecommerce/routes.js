import EcommerceLayout from '../Ecommerce/Layout'
import WebInfoForm from '../Ecommerce/Web/Form'

export default [
    {
        path: 'web_information',
        alias: '',
        component: EcommerceLayout,
        name: 'Ecommerce',
        redirect: 'information',
        meta: {
            breadcrumb: 'Ecommerce'
        },
        children: [
            {
                path: 'web_information',
                alias: '',
                component: WebInfoForm,
                name: 'WebInfoForm',
                meta: {
                    breadcrumb: 'Datos'
                }
            },
        ]
    }]
