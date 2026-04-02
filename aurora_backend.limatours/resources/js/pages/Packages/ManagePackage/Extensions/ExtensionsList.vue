<template>
    <div class="row col-12">
        <div class="col-5">
            <label class="col-sm-12 col-form-label">Extensiones Disponibles</label>
            <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                <input class="form-control" id="search_extensions" type="search" v-model="query" value="">
            </div>
            <ul class="style_list_ul" id="list_extensions" >
                <draggable :list="extensions">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':extension.selected}" :id="'extension_'+index"
                        @click="selectExtension(extension,index)" v-for="(extension,index) in extensions">
                        <span v-if="extension.extension.code">
                            <span class="style_span_li">{{ extension.extension.code }} - <span v-html="extension.extension.translations[0].name"></span></span>
                        </span>
                        <span v-if="!(extension.extension.code)">
                            <span class="style_span_li">{{ extension.code }} - <span v-html="extension.translations[0].name"></span></span>
                        </span>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-1 mt-4 mr-2">
            <div class="col-12">
                <button @click="moveOneExtension()" class="btn btn-secondary mover_controls btn-block pr-3"
                    :disabled="!(extensions.length > 0)">
                    <font-awesome-icon :icon="['fas', 'angle-right']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="moveAllExtensions()" class="btn btn-secondary mover_controls btn-block pr-3"
                        :disabled="!(extensions.length > 0)">
                    <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="inverseOneExtension()" class="btn btn-secondary mover_controls btn-block pr-3"
                        :disabled="!(extensions_selected.length > 0)">
                    <font-awesome-icon :icon="['fas', 'angle-left']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="inverseAllExtensions()" class="btn btn-secondary mover_controls btn-block pr-3"
                        :disabled="!(extensions_selected.length > 0)">
                    <font-awesome-icon :icon="['fas', 'angle-double-left']"/>
                </button>
            </div>
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label">Extensiones relacionadas</label>
            <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                <input class="form-control" id="search_extensions_selected" type="search" v-model="query_extensions_selected"
                       value="">
            </div>
            <ul class="style_list_ul" id="list_extensions_selected">
                <draggable :list="extensions_selected" class="list-group">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':extension.selected}"
                        @click="selectExtensionExtensionsSelected(extension,index)" v-for="(extension,index) in extensions_selected">
                        <span v-if="extension.extension.code">
                            <span class="style_span_li">{{ extension.extension.code }} - <span v-html="extension.extension.translations[0].name"></span></span>
                        </span>
                        <span v-if="!(extension.extension.code)">
                            <span class="style_span_li">{{ extension.code }} - <span v-html="extension.translations[0].name"></span></span>
                        </span>

                    </li>
                </draggable>
            </ul>
        </div>

    </div>
</template>
<script>
  import { API } from './../../../../api'
  import draggable from 'vuedraggable'
  import TableClient from './.././../../../components/TableClient'

  export default {
    components: {
      draggable,
      'table-client': TableClient,
    },
    data () {
      return {
        scroll_limit: 2900,
        extensions: [],
        page: 1,
        nameSelectExtension: '',
        limit: 100,
        count: 0,
        num_pages: 1,
        query: '',
        interval: null,
        extensions_selected: [],
        page_extensions_selected: 1,
        limit_extensions_selected: 100,
        count_extensions_selected: 0,
        num_pages_extensions_selected: 1,
        query_extensions_selected: '',
        scroll_limit_extensions_selected: 2900,
        interval_extensions_selected: null,
        loading: false
      }
    },
    mounted: function () {

      this.getExtensions()
      this.getExtensionsSelected()

      let search_extensions = document.getElementById('search_extensions')
      let timeout_extensions
      search_extensions.addEventListener('keydown', () => {
        clearTimeout(timeout_extensions)
        timeout_extensions = setTimeout(() => {
          this.getExtensions()
          clearTimeout(timeout_extensions)
        }, 1000)
      })

      let search_extensions_selected = document.getElementById('search_extensions_selected')
      let timeout_extensions_selected
      search_extensions_selected.addEventListener('keydown', () => {
        clearTimeout(timeout_extensions_selected)
        timeout_extensions_selected = setTimeout(() => {
          this.getExtensionsSelected()
          clearTimeout(timeout_extensions_selected)
        }, 1000)
      })

      this.interval = setInterval(this.getScrollTop, 3000)
      this.interval_extensions_selected = setInterval(this.getScrollTopExtensionsSelected, 3000)
    },
    methods: {
      showError: function (title, text, isLoading = true) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: title,
          text: text
        })
        if (isLoading === true) {
          this.loading = true
        }
      },
      selectExtension: function (extension, index) {
        if (this.extensions[index].selected) {
          this.$set(this.extensions[index], 'selected', false)
        } else {
          this.nameSelectExtension = extension.name
          this.setPropertySelectedInExtensions()
          this.$set(this.extensions[index], 'selected', true)
        }
      },
        selectExtensionExtensionsSelected: function (extension, index) {
          if (this.extensions_selected[index].selected) {
            this.$set(this.extensions_selected[index], 'selected', false)
          } else {
            this.setPropertySelectedInExtensionsSelected()
            this.$set(this.extensions_selected[index], 'selected', true)
          }
        },
        searchSelectExtension: function () {
          for (let i = 0; i < this.extensions.length; i++) {
            if (this.extensions[i].selected) {
              return i
              break
            }
          }
          return -1
        },
        searchSelectExtensionExtensionsSelected: function () {
          for (let i = 0; i < this.extensions_selected.length; i++) {
            if (this.extensions_selected[i].selected) {
              return i
              break
            }
          }
          return -1
        },
      setPropertySelectedInExtensions: function () {
        for (let i = 0; i < this.extensions.length; i++) {
          this.$set(this.extensions[i], 'selected', false)
        }
      },
        setPropertySelectedInExtensionsSelected: function () {
          for (let i = 0; i < this.extensions_selected.length; i++) {
            this.$set(this.extensions_selected[i], 'selected', false)
          }
        },
        moveOneExtension: function () {

          if (this.loading === false) {

              this.loading = true
              let search_extension = this.searchSelectExtension()

              if (search_extension !== -1) {
                API({
                  method: 'post',
                  url: 'package/' + this.$route.params.package_id + '/extensions' + '?lang=' +
                    localStorage.getItem('lang'),
                  data: {
                    data: this.extensions[search_extension]
                  }
                })
                  .then((result) => {
                    if (result.data.success === true) {

                      this.$set(this.extensions[search_extension], 'selected', false)
                      this.extensions_selected.push(this.extensions[search_extension])
                      this.extensions.splice(search_extension, 1)
                      this.loading = false
                    }
                  }).catch(() => {
                  this.showError(
                    'Relación de Extensiones',
                    'Error de conexión'
                  )
                })
              } else {
                if (this.extensions.length > 0) {
                  let element = this.extensions.shift()
                  API({
                    method: 'post',
                    url: 'package/' + this.$route.params.package_id + '/extensions' + '?lang=' +
                      localStorage.getItem('lang'),
                    data: {
                      data: element
                    }
                  })
                    .then((result) => {
                      if (result.data.success === true) {
                        this.extensions_selected.push(element)
                        this.loading = false
                      }
                    }).catch((e) => {
                    this.showError(
                      'Relación de Extensiones',
                      'Error de conexión' + e
                    )
                  })
                }
              }

          } else {
            console.log('Bloqueado accion')
          }
        },
        inverseOneExtension: function () {
          if (this.loading === false) {
              this.loading = true

              let search_extension = this.searchSelectExtensionExtensionsSelected()
              if (search_extension !== -1) {

                API({
                  method: 'post',
                  url: 'package/' + this.$route.params.package_id + '/extensions/inverse' + '?lang=' +
                    localStorage.getItem('lang'),
                  data: {
                    data: this.extensions_selected[search_extension]
                  }
                })
                  .then((result) => {
                    if (result.data.success === true) {
                      this.getExtensions()
                      this.getExtensionsSelected()
                      this.loading = false
                    }
                  }).catch(() => {
                  this.showError(
                    'Relación de Extensiones',
                    'Error de conexión'
                  )
                })
              } else {
                if (this.extensions_selected.length > 0) {
                  this.loading = true
                  let element = this.extensions_selected.shift()
                  API({
                    method: 'post',
                    url: 'package/' + this.$route.params.package_id + '/extensions/inverse' + '?lang=' +
                      localStorage.getItem('lang'),
                    data: {
                      data: element
                    }
                  })
                    .then((result) => {
                      if (result.data.success === true) {
                        this.extensions.push(element)
                        this.loading = false
                      }
                    }).catch((e) => {
                    this.showError(
                      'Relación de Extensiones',
                      'Error de conexión' + e
                    )
                  })
                }
              }
            }
        },
        moveAllExtensions: function () {
          if (this.loading === false) {

              this.loading = true

              if (this.extensions.length > 0) {
                for (let i = 0; i < this.extensions.length; i++) {
                  this.$set(this.extensions[i], 'selected', false)
                  this.extensions_selected.push(this.extensions[i])
                }
                this.extensions = []

                API({
                  method: 'post',
                  url: 'package/' + this.$route.params.package_id + '/extensions/all' + '?lang=' +
                    localStorage.getItem('lang'),
                  data: {
                    package_id: this.$route.params.package_id
                  }
                })
                  .then((result) => {
                    if (result.data.success === true) {
                      this.getExtensionsSelected()
                      this.loading = false
                    }
                  }).catch((e) => {
                  this.showError(
                    'Relación de Extensiones',
                    'Error de conexión' + e
                  )
                })
              }
          }
        },
        inverseAllExtensions: function () {
          if (this.loading === false) {

              this.loading = true
              if (this.extensions_selected.length > 0) {
                for (let i = 0; i < this.extensions_selected.length; i++) {

                  this.extensions.push(this.extensions_selected[i])
                }
                this.extensions_selected = []
                API({
                  method: 'post',
                  url: 'package/' + this.$route.params.package_id + '/extensions/inverse/all' + '?lang=' +
                    localStorage.getItem('lang')
                })
                  .then((result) => {
                    if (result.data.success === true) {
                      this.getExtensions()
                      this.loading = false
                    }
                  }).catch((e) => {
                  this.showError(
                    'Relación de Extensiones',
                    'Error de conexión' + e
                  )
                })
              }
            }
        },
      calculateNumPages: function (num_extensions, limit) {
        this.num_pages = Math.ceil(num_extensions / limit)
      },
        calculateNumPagesExtensionsSelected: function (num_extensions, limit) {
          this.num_pages_extensions_selected = Math.ceil(num_extensions / limit)
        },
        getScrollTop: function () {
          let scroll = (document.getElementById('list_extensions'))
                        ? document.getElementById('list_extensions').scrollTop : 0
          if (scroll > this.scroll_limit) {
            console.log(scroll)
            this.page += 1
            this.scroll_limit = 2900 * this.page
            if (this.page === this.num_pages) {
              clearInterval(this.interval)
              this.getExtensionsScroll()
            } else {

              this.getExtensionsScroll()
            }

          }
        },
        getScrollTopExtensionsSelected: function () {
          let scroll = (document.getElementById('list_extensions_selected'))
            ? document.getElementById('list_extensions_selected').scrollTop : 0
          if (scroll > this.scroll_limit_extensions_selected) {
            this.page_extensions_selected += 1
            this.scroll_limit_extensions_selected = 2900 * this.page_extensions_selected
            if (this.page_extensions_selected === this.num_pages_extensions_selected) {
              clearInterval(this.interval_extensions_selected)
              this.getExtensionsScrollSelected()
            } else {

              this.getExtensionsScrollSelected()
            }

          }
        },
      getExtensions () {

        API({
          method: 'post',
          url: 'package/' + this.$route.params.package_id + '/extensions/unassigned' + '?lang=' +
            localStorage.getItem('lang'),
          data: {
            page: 1,
            limit: this.limit,
            query: this.query
          }
        })
          .then((result) => {
            this.extensions = result.data.data
            this.count = result.data.count
            this.calculateNumPages(result.data.count, this.limit)
            this.scroll_limit = 2900
            document.getElementById('list_extensions').scrollTop = 0

          }).catch(() => {
          this.showError(
            'Relación de Extensiones',
            'Error de conexión'
          )
        })

      },
        getExtensionsScroll: function () {

          API({
            method: 'post',
            url: 'package/' + this.$route.params.package_id + '/extensions/unassigned' + '?lang=' +
              localStorage.getItem('lang'),
            data: {
              page: this.page,
              limit: this.limit,
              query: this.query
            }
          })
            .then((result) => {
              let extensions = result.data.data
              for (let i = 0; i < extensions.length; i++) {
                this.extensions.push(extensions[i])
              }
              if (this.page === 1) {
                this.count = result.data.count
                this.calculateNumPages(result.data.count, this.limit)
              }
            }).catch(() => {
            this.showError(
              'Relación de Extensiones',
              'Error de conexión'
            )
          })

        },
        getExtensionsSelected: function () {
          API({
            method: 'post',
            url: 'package/' + this.$route.params.package_id + '/extensions/assigned' + '?lang=' +
              localStorage.getItem('lang'),
            data: {
              page: 1,
              limit: this.limit_extensions_selected,
              query: this.query_extensions_selected
            }
          })
            .then((result) => {
              this.extensions_selected = result.data.data

              this.count_extensions_selected = result.data.count
              this.calculateNumPagesExtensionsSelected(result.data.count, this.limit_extensions_selected)
              this.scroll_limit_extensions_selected = 2900
              document.getElementById('list_extensions_selected').scrollTop = 0

            }).catch((e) => {
            this.showError(
              'Relación de Extensiones',
              'Error de conexión' + e
            )
          })
        },
      getExtensionsScrollSelected: function () {
        API({
          method: 'post',
          url: 'package/' + this.$route.params.package_id + '/extensions/assigned' + '?lang=' +
            localStorage.getItem('lang'),
          data: {
            page: this.page_extensions_selected,
            limit: this.limit_extensions_selected,
            query: this.query_extensions_selected
          }
        })
          .then((result) => {
            let extensions_selected = result.data.data
            for (let i = 0; i < extensions_selected.length; i++) {
              this.extensions_selected.push(extensions_selected[i])
            }
            if (this.page === 1) {
              this.count = result.data.count
              this.calculateNumPagesExtensionsSelected(result.data.count, this.limit)
            }
          }).catch(() => {
          this.showError(
            'Relación de Extensiones',
            'Error de conexión'
          )
        })
       }
      }
  }
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .style_list_ul {
        height: 160px;
        max-height: 160px;
        overflow-y: scroll;
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .selected {
        background-color: #005ba5;
        color: white;
    }

    .style_list_li {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
        cursor: move;
    }

    .style_span_li {
        margin-left: 5px;
        font-size: 11px;
    }

    #search_extensions:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_extensions {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    .button_icon {
        background-color: #f0f3f5 !important;
        border-top-left-radius: 0.2rem;
        color: #000;
        cursor: default !important;
    }

    .button_icon:hover {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:focus {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:active {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .mover_controls {
        padding: 10px;
        margin-bottom: 10px;
    }
</style>


