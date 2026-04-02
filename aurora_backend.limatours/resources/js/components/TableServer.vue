<script>
  export default {
    props: {
      columns: {
        type: Array,
        required: true
      },
      data: {
        type: Array
      },
      options: {
        type: Object,
        required: true
      },
      id: {
        default: 'dataTable'
      },
      theme: {
        default: 'bootstrap4'
      },
      url: {
        type: String
      }
    },
    data () {
      return {
        defaultOptions: {
          headings: {
            id: 'ID',
            actions: 'Acciones'
          },
          sortable: ['id'],
          filterable: ['id'],
          texts: {}
        }
      }
    },
    methods: {
      renderTableServer (h) {
        return h(
          'v-server-table',
          {
            ref: 'tableserver',
            props: {
              columns: this.columns,
              data: this.data,
              options: this.setOptions,
              id: this.id,
              theme: this.theme,
              url: this.url
            },
            scopedSlots: this.$vnode.data.scopedSlots
          }
        )
      }
    },
    computed: {
      setColumnsClasses: {
        get () {
          if (this.loading) {
            return ['loading']
          } else {
            let cssClasses = {}
            this.columns.forEach((item) => {
              cssClasses[item] = 'vueTable_column_' + item
            })
            return cssClasses
          }
        }
      },
      setOptions: {
        get () {
          let newOptions = {}
          Object.assign(newOptions, this.defaultOptions, this.options)

          newOptions.columnsClasses = this.setColumnsClasses

          newOptions.texts = {
            count: this.$i18n.t('global.table.count'),
            first: this.$i18n.t('global.table.first'),
            last: this.$i18n.t('global.table.last'),
            filter: this.$i18n.t('global.table.filter'),
            filterPlaceholder: this.$i18n.t('global.table.filterPlaceholder'),
            limit: this.$i18n.t('global.table.limit'),
            page: this.$i18n.t('global.table.page'),
            noResults: this.$i18n.t('global.table.noResults'),
            filterBy: this.$i18n.t('global.table.filterBy'),
            loading: this.$i18n.t('global.table.loading'),
            defaultOption: this.$i18n.t('global.table.defaultOption'),
            columns: this.$i18n.t('global.table.columns')
          }
          return newOptions
        }
      }
    },
    render (h) {
      return h(
        'div',
        {
          class: {}
        },
        [
          this.renderTableServer(h)
        ]
      )
    }
  }
</script>
