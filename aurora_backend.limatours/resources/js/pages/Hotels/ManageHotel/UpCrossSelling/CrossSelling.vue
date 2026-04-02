<template>
    <div class="container-fluid">
        <div class="row col-12" style="margin: 0;" v-show="!loading">
            <div class="col-5">
                <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                    <input class="form-control" id="search_services" type="search" v-model="query" value="">
                </div>
                <ul class="style_list_ul" id="list_services" ref="list_services">
                    <draggable :list="services">
                        <li :class="{'style_list_li':true, 'item':true, 'selected':service.selected}"
                            :id="'service_'+index"
                            :key="service.id"
                            @click="selectService(service,index)" v-for="(service,index) in services">
                            <span class="style_span_li">{{ service.name}}</span>
                        </li>
                    </draggable>
                </ul>
            </div>
            <div class="col-2">
                <div class="col-12">
                    <button @click="moveOneService()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-right']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="moveAllServices()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="inverseOneHotel()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-left']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="inverseAllServices()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-double-left']"/>
                    </button>
                </div>
            </div>
            <div class="col-5">
                <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                    <input class="form-control" id="search_services_selected" type="search"
                           v-model="query_services_selected"
                           value="">
                </div>
                <ul class="style_list_ul" id="list_services_selected" ref="list_services_selected">
                    <draggable :list="services_selected" class="list-group">
                        <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}"
                            @click="selectServiceServicesSelected(hotel,index)"
                            v-for="(hotel,index) in services_selected">
                            <span class="style_span_li">{{ hotel.name}}</span>
                        </li>
                    </draggable>
                </ul>
            </div>
        </div>
        <div class="table-loading text-center" v-show="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </div>
</template>
<script>
  import { API } from './../../../../api'
  import draggable from 'vuedraggable'

  export default {
    components: {
      draggable
    },
    data () {
      return {
        users: [],
        scroll_limit: 2900,
        services: [],
        page: 1,
        limit: 100,
        count: 0,
        num_pages: 1,
        query: '',
        interval: null,
        services_selected: [],
        page_services_selected: 1,
        limit_services_selected: 100,
        count_services_selected: 0,
        num_pages_services_selected: 1,
        query_services_selected: '',
        scroll_limit_services_selected: 2900,
        interval_services_selected: null,
        loading: false
      }
    },
    computed: {},
    mounted: function () {
      this.getServices()
      this.getServicesSelected()
      let search_services = document.getElementById('search_services')
      let timeout_services
      search_services.addEventListener('keydown', () => {
        clearTimeout(timeout_services)
        timeout_services = setTimeout(() => {
          this.getServices()
          clearTimeout(timeout_services)
        }, 1000)
      })

      let search_services_selected = document.getElementById('search_services_selected')
      let timeout_services_selected
      search_services_selected.addEventListener('keydown', () => {
        clearTimeout(timeout_services_selected)
        timeout_services_selected = setTimeout(() => {
          this.getServicesSelected()
          clearTimeout(timeout_services_selected)
        }, 1000)
      })

      this.interval = setInterval(this.getScrollTop, 3000)
      this.interval_services_selected = setInterval(this.getScrollTopHotelsSelected, 3000)
    },
    methods: {
      selectService: function (service, index) {
        if (this.services[index].selected) {
          this.$set(this.services[index], 'selected', false)
        } else {
          this.setPropertySelectedInServices()
          this.$set(this.services[index], 'selected', true)
        }
      },
      selectServiceServicesSelected: function (service, index) {
        if (this.services_selected[index].selected) {
          this.$set(this.services_selected[index], 'selected', false)
        } else {
          this.setPropertySelectedInServicesSelected()
          this.$set(this.services_selected[index], 'selected', true)
        }
      },
      searchSelectService: function () {
        for (let i = 0; i < this.services.length; i++) {
          if (this.services[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      searchSelectServiceServicesSelected: function () {
        for (let i = 0; i < this.services_selected.length; i++) {
          if (this.services_selected[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      setPropertySelectedInServices: function () {
        for (let i = 0; i < this.services.length; i++) {
          this.$set(this.services[i], 'selected', false)
        }
      },
      setPropertySelectedInServicesSelected: function () {
        for (let i = 0; i < this.services_selected.length; i++) {
          this.$set(this.services_selected[i], 'selected', false)
        }
      },
      moveOneService: function () {
        if (this.loading === false) {
          if (this.services.length > 0) {
            let search_service = this.searchSelectService()
            if (search_service !== -1) {
              this.loading = true
              API({
                method: 'post',
                url: 'cross_selling/store',
                data: this.services[search_service]
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getServices()
                    this.getServicesSelected()
                    this.loading = false
                  }
                }).catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                  text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
                })
              })
            } else {
              this.loading = true
              let element = this.services.shift()
              API({
                method: 'post',
                url: 'cross_selling/store',
                data: element
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getServices()
                    this.getServicesSelected()
                    this.loading = false
                  }
                }).catch((e) => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                  text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
                })
              })
            }
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      inverseOneHotel: function () {
        if (this.loading === false) {
          if (this.services_selected.length > 0) {
            let search_service = this.searchSelectServiceServicesSelected()
            if (search_service !== -1) {
              this.loading = true
              API({
                method: 'post',
                url: 'cross_selling/inverse',
                data: this.services_selected[search_service]
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.getServices()
                    this.getServicesSelected()
                    this.loading = false
                  }
                }).catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                  text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
                })
              })
            } else {
              this.loading = true
              let element = this.services_selected.shift()
              API({
                method: 'post',
                url: 'cross_selling/inverse',
                data: element
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.services.push(element)
                    this.loading = false
                  }
                }).catch((e) => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                  text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
                })
              })
            }
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      moveAllServices: function () {
        if (this.loading === false) {
          if (this.services.length > 0) {
            this.loading = true
            API({
              method: 'post',
              url: 'cross_selling/store/all',
              data: {
                hotel_id: this.$route.params.hotel_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.services = []
                  this.getServices()
                  this.getServicesSelected()
                  this.loading = false
                }
              }).catch((e) => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
              })
            })
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      inverseAllServices: function () {
        if (this.loading === false) {
          if (this.services_selected.length > 0) {
            this.loading = true
            API({
              method: 'post',
              url: 'cross_selling/inverse/all',
              data: {
                hotel_id: this.$route.params.hotel_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.getServices()
                  this.getServicesSelected()
                  this.loading = false
                }
              }).catch((e) => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
                text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
              })
            })
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      calculateNumPages: function (num_services, limit) {
        this.num_pages = Math.ceil(num_services / limit)
      },
      calculateNumPagesServicesSelected: function (num_services, limit) {
        this.num_pages_services_selected = Math.ceil(num_services / limit)
      },
      getScrollTop: function () {
        if(!this.$refs.list_services) return

        let scroll = this.$refs.list_services.scrollTop

        if (scroll > this.scroll_limit) {
          this.page += 1
          this.scroll_limit = 2900 * this.page
          if (this.page === this.num_pages) {
            clearInterval(this.interval)
            this.getServicesScroll()
          } else {

            this.getServicesScroll()
          }

        }
      },
      getScrollTopHotelsSelected: function () {
        if(!this.$refs.list_services_selected) return

        let scroll = this.$refs.list_services_selected.scrollTop

        if (scroll > this.scroll_limit_services_selected) {
          this.page_services_selected += 1
          this.scroll_limit_services_selected = 2900 * this.page_services_selected
          if (this.page_services_selected === this.num_pages_services_selected) {
            clearInterval(this.interval_services_selected)
            this.getServicesScrollSelected()
          } else {

            this.getServicesScrollSelected()
          }

        }
      },
      getServices: function () {
        API({
          method: 'post',
          url: 'services/search/cross_selling',
          data: {
            page: 1,
            limit: this.limit,
            query: this.query,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            this.services = result.data.data
            this.count = result.data.count
            this.calculateNumPages(result.data.count, this.limit)
            this.scroll_limit = 2900
            document.getElementById('list_services').scrollTop = 0

          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
          })
        })

      },
      getServicesScroll: function () {

        API({
          method: 'post',
          url: 'services/search/cross_selling',
          data: {
            page: this.page,
            limit: this.limit,
            query: this.query,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            let services = result.data.data
            for (let i = 0; i < services.length; i++) {
              this.services.push(services[i])
            }
            if (this.page === 1) {
              this.count = result.data.count
              this.calculateNumPages(result.data.count, this.limit)
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
          })
        })

      },
      getServicesSelected: function () {
        API({
          method: 'post',
          url: 'cross_selling',
          data: {
            page: 1,
            limit: this.limit_services_selected,
            query: this.query_services_selected,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            this.services_selected = result.data.data
            this.count_services_selected = result.data.count
            this.calculateNumPagesServicesSelected(result.data.count, this.limit_services_selected)
            this.scroll_limit_services_selected = 2900
            document.getElementById('list_services_selected').scrollTop = 0

          }).catch((e) => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
          })
        })

      },
      getServicesScrollSelected: function () {

        API({
          method: 'post',
          url: 'cross_selling',
          data: {
            page: this.page_services_selected,
            limit: this.limit_services_selected,
            query: this.query_services_selected,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            let services_selected = result.data.data
            for (let i = 0; i < services_selected.length; i++) {
              this.services_selected.push(services_selected[i])
            }
            if (this.page === 1) {
              this.count = result.data.count
              this.calculateNumPagesServicesSelected(result.data.count, this.limit)
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
          })
        })

      },
    },
    beforeDestroy () {

      clearInterval(this.interval)
      clearInterval(this.interval_services_selected)

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
    }

    #search_services:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_services {
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


