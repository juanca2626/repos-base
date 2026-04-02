<template>
    <table-client :columns="table.columns" :data="markets" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-category" slot="category" slot-scope="props">
            <div v-if="props.row.category.translations.length > 0">
                {{ props.row.category.translations[0].value }}
            </div>
        </div>

        <div class="table-question" slot="question" slot-scope="props">
            <div v-if="props.row.translations_question.length > 0">
                {{ props.row.translations_question[0].value }}
            </div>
        </div>

        <div class="table-answer" slot="answer" slot-scope="props">
            <div v-if="props.row.translations_question.length">
                {{ props.row.translations_answer[0].value }}
            </div>
        </div>


        <div class="table-loading text-center" slot="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </table-client>
</template>

<script>
    import { API } from './../../api'
    import TableClient from './../../components/TableClient'
    import MenuEdit from './../../components/MenuEdit'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

    export default {
        components: {
            'table-client': TableClient,
            'menu-edit': MenuEdit,
            BFormCheckbox
        },
        data: () => {
            return {
                loading: false,
                markets: [],
                table: {
                    columns: ['actions', 'id', 'category', 'question', 'answer']
                }
            }
        },
        mounted () {
            this.fetchData()
        },
        computed: {
            menuOptions: function () {
                let options = []
                if (this.$can('update', 'frequentquestions')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: 'frequent_questions/edit/',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'link'
                    })
                }
                if (this.$can('delete', 'frequentquestions')) {
                    options.push({
                        type: 'delete',
                        text: '',
                        link: '',
                        icon: 'trash',
                        type_action: 'button',
                        callback_delete: 'remove'
                    })
                }
                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        category: 'Categoria',
                        question: 'Pregunta',
                        answer: 'Respuesta',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id', 'category', 'question', 'answer']
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData(payload.lang)
            })
        },
        methods: {
            checkboxChecked: function (market_status) {
                if (market_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },

            fetchData: function () {
                //this.loading = true
                API.get('frequent_questions?lang=' + localStorage.getItem('lang')).then((result) => {
                    console.log(result.data)
                    if (result.data.success === true) {
                        this.markets = result.data.data
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
                    .catch((error) => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: error.response.data.title,
                            text: error.response.data.message
                        })
                    })
            },
            remove (id) {
                API({
                    method: 'DELETE',
                    url: 'frequent_questions/' + id
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.frequentquestions'),
                                text: result.data.message
                            })
                        } else {
                            this.fetchData()
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.frequentquestions'),
                                text: this.$t('inclusions.error.messages.inclusion_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.frequentquestions'),
                        text: this.$t('markets.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


