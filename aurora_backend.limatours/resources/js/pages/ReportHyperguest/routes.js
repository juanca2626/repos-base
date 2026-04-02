import ReportHyperguest from './Lista'
import FormReport from './Form'
import LayoutReports from './Layout'


export default [
    {
        path: 'report-hyperguest',
        alias: '',
        component: LayoutReports,
        redirect: '/report-hyperguest/list',
        name: 'Reporte Hyperguest',
        meta: {
            breadcrumb: 'Reporte'
        },
        children: [
           
            {
                path: 'list',
                alias: '',
                component: ReportHyperguest,
                name: 'ReportHyperguest',
                meta: {
                    breadcrumb: 'Lista',
                },
            },
            {
                path: 'add',
                alias: '',
                component: FormReport,
                name: 'createReportHyperguest',                
                meta: {
                    breadcrumb: 'Agregar',
                },
            },

        
        ]
    }]
