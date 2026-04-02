<script>
export default {
  props: {
    columns: {
      type: Array,
      required: true
    },
    data: {
      type: Array,
      required: true
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
    loading: {
      type: Boolean,
      default: false
    },
    draggable: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      defaultOptions: {
        headings: {
          id: 'ID',
          actions: 'Editar'
        },
        sortable: ['id'],
        filterable: ['id'],
        texts: {}
      }
      // Ya no necesitamos internalData porque usamos .sync
    }
  },
  methods: {
    renderTableClient (h) {
      return h(
        'v-client-table',
        {
          ref: 'table-client',
          props: {
            columns: this.setColumns,
            data: this.setData,
            options: this.setOptions,
            id: this.id,
            theme: this.theme
          },
          scopedSlots: this.$vnode.data.scopedSlots
        }
      )
    },

    reorderRows(fromIndex, toIndex) {
      if (fromIndex === toIndex) return

      // Crear nueva copia del array
      const newData = [...this.data]
      const [movedItem] = newData.splice(fromIndex, 1)
      newData.splice(toIndex, 0, movedItem)

      // Emitir el evento update:data para actualizar el .sync
      this.$emit('update:data', newData)

      // También emitir el evento personalizado por si lo necesitas
      this.$emit('rows-reordered', {
        fromIndex,
        toIndex,
        movedItem,
        newData: [...newData]
      })
    },

    setupDragAndDrop() {
      if (!this.draggable || this.loading) return

      const table = this.$el.querySelector('table')
      if (!table) return

      const tbody = table.querySelector('tbody')
      if (!tbody) return

      const rows = tbody.querySelectorAll('tr')

      rows.forEach((row, index) => {
        // Limpiar event listeners anteriores
        row.removeEventListener('dragstart', this.handleDragStart)
        row.removeEventListener('dragover', this.handleDragOver)
        row.removeEventListener('drop', this.handleDrop)
        row.removeEventListener('dragenter', this.handleDragEnter)
        row.removeEventListener('dragleave', this.handleDragLeave)

        // Configurar la fila como draggable
        row.setAttribute('draggable', 'true')

        // Agregar nuevos event listeners
        row.addEventListener('dragstart', (e) => this.handleDragStart(e, index))
        row.addEventListener('dragover', this.handleDragOver)
        row.addEventListener('drop', (e) => this.handleDrop(e, index))
        row.addEventListener('dragenter', this.handleDragEnter)
        row.addEventListener('dragleave', this.handleDragLeave)
      })
    },

    handleDragStart(e, index) {
      this.dragStartIndex = index
      e.dataTransfer.effectAllowed = 'move'
      e.dataTransfer.setData('text/plain', index)
      e.target.classList.add('dragging')
    },

    handleDragOver(e) {
      e.preventDefault()
      e.dataTransfer.dropEffect = 'move'
      return false
    },

    handleDragEnter(e) {
      e.preventDefault()
      e.target.classList.add('drag-over')
    },

    handleDragLeave(e) {
      e.target.classList.remove('drag-over')
    },

    handleDrop(e, dropIndex) {
      e.preventDefault()
      e.stopPropagation()

      // Remover clases visuales
      const rows = this.$el.querySelectorAll('tbody tr')
      rows.forEach(row => {
        row.classList.remove('drag-over', 'dragging')
      })

      if (this.dragStartIndex !== null && this.dragStartIndex !== dropIndex) {
        this.reorderRows(this.dragStartIndex, dropIndex)
      }

      this.dragStartIndex = null
      return false
    },

    addDragHandleColumn(columns) {
      if (!this.draggable || this.loading || columns.includes('_dragHandle')) {
        return columns
      }
      return ['_dragHandle', ...columns]
    },

    prepareDataWithDragHandle(data) {
      if (!this.draggable || this.loading) {
        return data
      }

      return data.map((item, index) => ({
        _dragHandle: `handle-${index}`,
        ...item
      }))
    }
  },
  computed: {
    setColumns: {
      get () {
        const baseColumns = this.loading ? ['loading'] : this.columns
        return this.addDragHandleColumn(baseColumns)
      }
    },

    setColumnsClasses: {
      get () {
        if (this.loading) {
          return ['loading']
        } else {
          let cssClasses = {}
          const columns = this.setColumns

          columns.forEach((item) => {
            cssClasses[item] = 'vueTable_column_' + item
            if (item === '_dragHandle') {
              cssClasses[item] += ' drag-handle-column'
            }
          })
          return cssClasses
        }
      }
    },

    setData: {
      get () {
        if (this.loading) {
          return [{ loading: '' }]
        } else if (this.draggable) {
          return this.prepareDataWithDragHandle(this.data) // Usamos this.data directamente
        } else {
          return this.data // Usamos this.data directamente
        }
      }
    },

    setOptions: {
      get () {
        let newOptions = {}
        Object.assign(newOptions, this.defaultOptions, this.options)

        if (this.loading) {
          newOptions.headings = { loading: '' }
          newOptions.sortable = []
          newOptions.filterable = ['loading']
        } else if (this.draggable) {
          newOptions.headings = {
            _dragHandle: '',
            ...newOptions.headings
          }

          newOptions.templates = {
            ...newOptions.templates,
            _dragHandle: (h, row) => {
              return h('div', {
                class: 'drag-handle'
              }, '⋮⋮')
            }
          }

          if (newOptions.sortable) {
            newOptions.sortable = newOptions.sortable.filter(col => col !== '_dragHandle')
          }
          if (newOptions.filterable) {
            newOptions.filterable = newOptions.filterable.filter(col => col !== '_dragHandle')
          }
        }

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

  mounted() {
    this.setupDragAndDrop()
  },

  updated() {
    this.setupDragAndDrop()
  },

  render (h) {
    return h(
      'div',
      {
        class: 'w-100 draggable-table-container'
      },
      [
        this.renderTableClient(h)
      ]
    )
  }
}
</script>

<style scoped>
.drag-instructions {
  text-align: center;
  padding: 8px;
  background-color: #f8f9fa;
  border-radius: 4px;
  border: 1px dashed #dee2e6;
  margin-bottom: 10px;
}

/* Estilos para la tabla */
::v-deep .drag-handle {
  cursor: grab;
  padding: 8px 5px;
  user-select: none;
  color: #6c757d;
  font-size: 16px;
  font-weight: bold;
  display: inline-block;
  text-align: center;
  width: 100%;
}

::v-deep .drag-handle-column {
  width: 50px;
  min-width: 50px;
  max-width: 50px;
  text-align: center;
  vertical-align: middle;
  padding: 0 !important;
}

::v-deep .dragging {
  opacity: 0.5;
  background-color: #f8f9fa !important;
}

/*
::v-deep .drag-over {
  background-color: #e3f2fd !important;
  border-top: 2px solid #2196f3 !important;
}

::v-deep table tbody tr {
  cursor: move;
}
*/

::v-deep table tbody tr:hover {
  background-color: #f5f5f5;
}
</style>
