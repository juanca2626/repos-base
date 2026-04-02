<template>
    <div class="row col-12" style="margin: 0;">
        <div class="col-5">
            <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                <input class="form-control" id="search_hotels" type="search" v-model="query" value="">
            </div>
            <ul class="style_list_ul" id="list_hotels">
                <draggable :list="hotels">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}" :id="'hotel_'+index"
                        :key="hotel.id"
                        @click="selectHotel(hotel,index)" v-for="(hotel,index) in hotels">
                        <span class="style_span_li">{{ hotel.name}}</span>
                    </li>
                </draggable>
            </ul>
        </div>
        <div class="col-2">
            <div class="col-12">
                <button @click="moveOneHotel()" class="btn btn-secondary mover_controls btn-block">
                    <font-awesome-icon :icon="['fas', 'angle-right']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="moveAllHotels()" class="btn btn-secondary mover_controls btn-block">
                    <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="inverseOneHotel()" class="btn btn-secondary mover_controls btn-block">
                    <font-awesome-icon :icon="['fas', 'angle-left']"/>
                </button>
            </div>
            <div class="col-12">
                <button @click="inverseAllHotels()" class="btn btn-secondary mover_controls btn-block">
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
                <input class="form-control" id="search_hotels_selected" type="search" v-model="query_hotels_selected"
                       value="">
            </div>
            <ul class="style_list_ul" id="list_hotels_selected">
                <draggable :list="hotels_selected" class="list-group">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':hotel.selected}"
                        @click="selectHotelHotelsSelected(hotel,index)" v-for="(hotel,index) in hotels_selected">
                        <span class="style_span_li">{{ hotel.name}}</span>
                    </li>
                </draggable>
            </ul>
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
        hotels: [],
        page: 1,
        limit: 100,
        count: 0,
        num_pages: 1,
        query: '',
        interval: null,
        hotels_selected: [],
        page_hotels_selected: 1,
        limit_hotels_selected: 100,
        count_hotels_selected: 0,
        num_pages_hotels_selected: 1,
        query_hotels_selected: '',
        scroll_limit_hotels_selected: 2900,
        interval_hotels_selected: null,
        loading: false
      }
    },
    computed: {},
    mounted: function () {
      this.getHotels()
      this.getHotelsSelected()
      let search_hotels = document.getElementById('search_hotels')
      let timeout_hotels
      search_hotels.addEventListener('keydown', () => {
        clearTimeout(timeout_hotels)
        timeout_hotels = setTimeout(() => {
          this.getHotels()
          clearTimeout(timeout_hotels)
        }, 1000)
      })

      let search_hotels_selected = document.getElementById('search_hotels_selected')
      let timeout_hotels_selected
      search_hotels_selected.addEventListener('keydown', () => {
        clearTimeout(timeout_hotels_selected)
        timeout_hotels_selected = setTimeout(() => {
          this.getHotelsSelected()
          clearTimeout(timeout_hotels_selected)
        }, 1000)
      })

      this.interval = setInterval(this.getScrollTop, 3000)
      this.interval_hotels_selected = setInterval(this.getScrollTopHotelsSelected, 3000)
    },
    methods: {
      selectHotel: function (hotel, index) {
        if (this.hotels[index].selected) {
          this.$set(this.hotels[index], 'selected', false)
        } else {
          this.setPropertySelectedInHotels()
          this.$set(this.hotels[index], 'selected', true)
        }
      },
      selectHotelHotelsSelected: function (hotel, index) {
        if (this.hotels_selected[index].selected) {
          this.$set(this.hotels_selected[index], 'selected', false)
        } else {
          this.setPropertySelectedInHotelsSelected()
          this.$set(this.hotels_selected[index], 'selected', true)
        }
      },
      searchSelectHotel: function () {
        for (let i = 0; i < this.hotels.length; i++) {
          if (this.hotels[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      searchSelectHotelHotelsSelected: function () {
        for (let i = 0; i < this.hotels_selected.length; i++) {
          if (this.hotels_selected[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      setPropertySelectedInHotels: function () {
        for (let i = 0; i < this.hotels.length; i++) {
          this.$set(this.hotels[i], 'selected', false)
        }
      },
      setPropertySelectedInHotelsSelected: function () {
        for (let i = 0; i < this.hotels_selected.length; i++) {
          this.$set(this.hotels_selected[i], 'selected', false)
        }
      },
      moveOneHotel: function () {
        if (this.loading === false) {
          this.loading = true
          let search_hotel = this.searchSelectHotel()
          if (search_hotel !== -1) {
            API({
              method: 'post',
              url: 'up_selling/store',
              data: this.hotels[search_hotel]
            })
              .then((result) => {
                if (result.data.success === true) {

                  this.$set(this.hotels[search_hotel], 'selected', false)
                  this.hotels[search_hotel].up_selling_id = result.data.up_selling_id
                  this.hotels_selected.push(this.hotels[search_hotel])
                  this.hotels.splice(search_hotel, 1)
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
            if (this.hotels.length > 0) {
              this.loading = true
              let element = this.hotels.shift()
              API({
                method: 'post',
                url: 'up_selling/store',
                data: element
              })
                .then((result) => {
                  if (result.data.success === true) {
                    element.up_selling_id = result.data.up_selling_id
                    this.hotels_selected.push(element)

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
          this.loading = true

          let search_hotel = this.searchSelectHotelHotelsSelected()
          if (search_hotel !== -1) {

            API({
              method: 'post',
              url: 'up_selling/inverse',
              data: this.hotels_selected[search_hotel]
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.getHotels()
                  this.getHotelsSelected()
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
            if (this.hotels_selected.length > 0) {
              this.loading = true
              let element = this.hotels_selected.shift()
              API({
                method: 'post',
                url: 'up_selling/inverse',
                data: element
              })
                .then((result) => {
                  if (result.data.success === true) {
                    this.hotels.push(element)
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
      moveAllHotels: function () {
        if (this.loading === false) {

          this.loading = true

          if (this.hotels.length > 0) {
            for (let i = 0; i < this.hotels.length; i++) {
              this.$set(this.hotels[i], 'selected', false)
              this.hotels_selected.push(this.hotels[i])
            }
            this.hotels = []

            API({
              method: 'post',
              url: 'up_selling/store/all',
              data: {
                hotel_id: this.$route.params.hotel_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {

                  this.getHotelsSelected()
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
      inverseAllHotels: function () {
        if (this.loading === false) {
          this.loading = true
          if (this.hotels_selected.length > 0) {
            for (let i = 0; i < this.hotels_selected.length; i++) {

              this.hotels.push(this.hotels_selected[i])
            }
            this.hotels_selected = []
            API({
              method: 'post',
              url: 'up_selling/inverse/all',
              data: {
                hotel_id: this.$route.params.hotel_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.getHotels()
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
      calculateNumPages: function (num_hotels, limit) {
        this.num_pages = Math.ceil(num_hotels / limit)
      },
      calculateNumPagesHotelsSelected: function (num_hotels, limit) {
        this.num_pages_hotels_selected = Math.ceil(num_hotels / limit)
      },
      getScrollTop: function () {
        let scroll = document.getElementById('list_hotels').scrollTop
        if (scroll > this.scroll_limit) {
          this.page += 1
          this.scroll_limit = 2900 * this.page
          if (this.page === this.num_pages) {
            clearInterval(this.interval)
            this.getHotelsScroll()
          } else {

            this.getHotelsScroll()
          }

        }
      },
      getScrollTopHotelsSelected: function () {
        let scroll = document.getElementById('list_hotels_selected').scrollTop
        if (scroll > this.scroll_limit_hotels_selected) {
          this.page_hotels_selected += 1
          this.scroll_limit_hotels_selected = 2900 * this.page_hotels_selected
          if (this.page_hotels_selected === this.num_pages_hotels_selected) {
            clearInterval(this.interval_hotels_selected)
            this.getHotelsScrollSelected()
          } else {

            this.getHotelsScrollSelected()
          }

        }
      },
      getHotels: function () {
        API({
          method: 'post',
          url: 'hotel/search/up_selling',
          data: {
            page: 1,
            limit: this.limit,
            query: this.query,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            console.log(result.data.count)
            this.hotels = result.data.data
            this.count = result.data.count
            this.calculateNumPages(result.data.count, this.limit)
            this.scroll_limit = 2900
            document.getElementById('list_hotels').scrollTop = 0

          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error')
          })
        })

      },
      getHotelsScroll: function () {

        API({
          method: 'post',
          url: 'hotel/search/up_selling',
          data: {
            page: this.page,
            limit: this.limit,
            query: this.query,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            let hotels = result.data.data
            for (let i = 0; i < hotels.length; i++) {
              this.hotels.push(hotels[i])
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
      getHotelsSelected: function () {
        API({
          method: 'post',
          url: 'up_selling',
          data: {
            page: 1,
            limit: this.limit_hotels_selected,
            query: this.query_hotels_selected,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            console.log(result.data.count)
            this.hotels_selected = result.data.data
            this.count_hotels_selected = result.data.count
            this.calculateNumPagesHotelsSelected(result.data.count, this.limit_hotels_selected)
            this.scroll_limit_hotels_selected = 2900
            document.getElementById('list_hotels_selected').scrollTop = 0

          }).catch((e) => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelupcrossselling.error.messages.name'),
            text: this.$t('hotelsmanagehotelupcrossselling.error.messages.connection_error') + e
          })
        })

      },
      getHotelsScrollSelected: function () {

        API({
          method: 'post',
          url: 'up_selling',
          data: {
            page: this.page_hotels_selected,
            limit: this.limit_hotels_selected,
            query: this.query_hotels_selected,
            hotel_id: this.$route.params.hotel_id
          }
        })
          .then((result) => {
            let hotels_selected = result.data.data
            for (let i = 0; i < hotels_selected.length; i++) {
              this.hotels_selected.push(hotels_selected[i])
            }
            if (this.page === 1) {
              this.count = result.data.count
              this.calculateNumPagesHotelsSelected(result.data.count, this.limit)
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
      clearInterval(this.interval_hotels_selected)

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

    #search_hotels:focus {
        box-shadow: none;
        border-color: #ccc;
    }

    #search_hotels {
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


