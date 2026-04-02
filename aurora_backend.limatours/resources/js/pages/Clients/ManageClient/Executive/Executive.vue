<template>
    <div class="row col-12">
        <!--
         <div class="col-12">
          <div class="row">
            <div class="col-7 pull-right">
                <div class="b-form-group form-group">
                  <div class="form-row">
                      <label class="col-sm-3 col-form-label" for="market">{{ $t('clientsmanageclientexecutive.market')
                          }}</label>
                    <div class="col-sm-8">
                        <select @change="searchMarket" ref="market" class="form-control" id="market" required size="0" v-model="selectMarket">
                          <option value="">
                              {{ $t('clientsmanageclientexecutive.all') }}
                          </option>
                          <option :value="market.code" v-for="market in markets">
                              {{ market.label}}
                          </option>
                      </select>
                    </div>
                 </div>
                </div>
            </div>
            <div class="clearfix"></div>
          </div>
        </div>
        -->
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">Ejecutivos Asociados al cliente</label>
            <div class="input-group">
               <span class="input-group-append">
                  <button class="btn btn-outline-secondary button_icon" type="button">
                      <font-awesome-icon :icon="['fas', 'search']"/>
                  </button>
               </span>
                <input class="form-control" id="search_executives" type="search" v-model="query" value="">
            </div>
            <ul class="style_list_ul_executive" id="list_executives" ref="executivesList">
                <draggable :list="executives">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':executive.selected}"
                        :id="'executive_'+index"
                        @click="selectExecutive(executive, index)" v-for="(executive, index) in executives">
                        <span class="style_span_li">{{executive.user_id}} - ({{ executive.code}}) {{ executive.name}}</span>
                    </li>
                </draggable>
            </ul>
        </div>

        <div class="col-2 mt-4">
            <div class="col-12">
                <button @click="moveOneExecutive()" class="btn btn-secondary mover_controls btn-block">
                    <font-awesome-icon :icon="['fas', 'angle-right']"/>
                </button>
            </div>
            <!--            <div class="col-12">-->
            <!--                <button @click="moveAllExecutives()" class="btn btn-secondary mover_controls btn-block">-->
            <!--                    <font-awesome-icon :icon="['fas', 'angle-double-right']"/>-->
            <!--                </button>-->
            <!--            </div>-->
            <div class="col-12">
                <button @click="inverseOneExecutive()" class="btn btn-secondary mover_controls btn-block">
                    <font-awesome-icon :icon="['fas', 'angle-left']"/>
                </button>
            </div>
            <!--            <div class="col-12">-->
            <!--                <button @click="inverseAllExecutives()" class="btn btn-secondary mover_controls btn-block">-->
            <!--                    <font-awesome-icon :icon="['fas', 'angle-double-left']"/>-->
            <!--                </button>-->
            <!--            </div>-->
        </div>
        <div class="col-5">
            <label class="col-sm-12 col-form-label" for="period">{{ $t('clientsmanageclientexecutive.executives_added')
                }}</label>
            <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                <input class="form-control" id="search_executives_selected" type="search"
                       v-model="query_executives_selected"
                       value="">
            </div>
            <ul class="style_list_ul" id="list_executives_selected" ref="executivesListSelected">
                    <li :class="{'style_list_li':true, 'item':true, 'selected':executive.selected}"
                        @click="selectExecutiveExecutivesSelected(executive,index)"
                        v-for="(executive,index) in executives_selected">
                        <span class="style_span_li">{{executive.user_id}} - ({{ executive.code}}) {{ executive.name}}</span>
                        <button title="Recibe email de Reservas?" class="style_span_li" @click="toggle_use_email(executive)">
                            <i class="fa fa-check i-success" v-if="executive.use_email_reserve"></i>
                            <i class="fa fa-ban i-error" v-else></i>
                            <i class="fa fa-envelope"></i>
                        </button>
                    </li>
            </ul>
        </div>

    </div>
</template>
<script>
    import {API} from './../../../../api'
    import draggable from 'vuedraggable'

    export default {
        components: {
            draggable
        },
        data() {
            return {
                markets: [],
                marketSelected: [],
                selectMarket: '',
                market: null,
                marketSearch: '',
                users: [],
                scroll_limit: 2900,
                executives: [],
                page: 1,
                porcentage: '',
                markup: '',
                limit: 100,
                count: 0,
                num_pages: 1,
                query: '',
                interval: null,
                executives_selected: [],
                page_executives_selected: 1,
                limit_executives_selected: 100,
                count_executives_selected: 0,
                num_pages_executives_selected: 1,
                query_executives_selected: '',
                scroll_limit_executives_selected: 2900,
                interval_executives_selected: null,
                loading: false
            }
        },
        computed: {},
        mounted: function () {
            this.getExecutives()
            this.getExecutivesSelected()
            // this.getMarkets()
            let search_executives = document.getElementById('search_executives')
            let timeout_executives
            search_executives.addEventListener('keydown', () => {
                clearTimeout(timeout_executives)
                timeout_executives = setTimeout(() => {
                    this.getExecutives()
                    clearTimeout(timeout_executives)
                }, 1000)
            })

            let search_executives_selected = document.getElementById('search_executives_selected')
            let timeout_executives_selected
            search_executives_selected.addEventListener('keydown', () => {
                clearTimeout(timeout_executives_selected)
                timeout_executives_selected = setTimeout(() => {
                    this.getExecutivesSelected()
                    clearTimeout(timeout_executives_selected)
                }, 1000)
            })

            this.interval = setInterval(this.getScrollTop, 3000)
            this.interval_executives_selected = setInterval(this.getScrollTopExecutivesSelected, 3000)
        },
        methods: {
            toggle_use_email( executive ){
                console.log(executive)

                API({
                    method: 'put',
                    url: 'client_executive/'+ executive.executive_id + '/use_email_reserve',
                    data: { use_email_reserve : !(executive.use_email_reserve) }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            executive.use_email_reserve = !(executive.use_email_reserve)
                        }
                    }).catch((e) => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                            text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                        })
                })

            },
            searchMarket: function () {
                this.getExecutives()
                // this.getHotelsSelected()
            },
            selectExecutive: function (executive, index) {
                if (this.executives[index].selected) {
                    this.$set(this.executives[index], 'selected', false)
                } else {
                    this.setPropertySelectedInExecutives()
                    this.$set(this.executives[index], 'selected', true)
                }
            },
            selectExecutiveExecutivesSelected: function (executive, index) {
                if (this.executives_selected[index].selected) {
                    this.$set(this.executives_selected[index], 'selected', false)
                } else {
                    this.setPropertySelectedInExecutivesSelected()
                    this.$set(this.executives_selected[index], 'selected', true)
                }
                console.log(this.executives_selected[index].selected)
            },
            searchSelectExecutive: function () {
                for (let i = 0; i < this.executives.length; i++) {
                    if (this.executives[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            searchSelectExecutiveExecutivesSelected: function () {
                for (let i = 0; i < this.executives_selected.length; i++) {
                    if (this.executives_selected[i].selected) {
                        return i
                        break
                    }
                }
                return -1
            },
            setPropertySelectedInExecutives: function () {
                for (let i = 0; i < this.executives.length; i++) {
                    this.$set(this.executives[i], 'selected', false)
                }
            },
            setPropertySelectedInExecutivesSelected: function () {
                for (let i = 0; i < this.executives_selected.length; i++) {
                    this.$set(this.executives_selected[i], 'selected', false)
                }
            },
            moveOneExecutive: function () {
                if (this.loading === false) {
                    this.loading = true
                    let search_executive = this.searchSelectExecutive()
                    let foundExecutive = this.executives[search_executive];
                    foundExecutive.region_id = this.$route.params.region_id;

                    if (search_executive !== -1) {
                        API({
                            method: 'post',
                            url: 'client_executive/store',
                            data: foundExecutive
                        })
                            .then((result) => {
                                if (result.data.success === true) {
                                    this.getExecutivesSelected()
                                    this.getExecutives()
                                    // this.$set(this.executives[search_executive], 'selected', false)
                                    // this.executives[search_executive].executive_id = result.data.executive_id
                                    // this.executives[search_executive].use_email_reserve = true
                                    // this.executives_selected.push(this.executives[search_executive])
                                    // this.executives.splice(search_executive, 1)
                                    this.loading = false
                                }
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                            })
                        })
                    } else {
                        if (this.executives.length > 0) {
                            this.loading = true
                            let element = this.executives.shift()
                            API({
                                method: 'post',
                                url: 'client_executive/store',
                                data: element
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        element.executive_id = result.data.executive_id
                                        this.executives_selected.push(element)
                                        this.getExecutivesSelected()
                                        this.getExecutives()

                                        this.loading = false
                                    }
                                }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                    text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                                })
                            })
                        }
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            inverseOneExecutive: function () {
                if (this.loading === false) {
                    this.loading = true

                    let search_executive = this.searchSelectExecutiveExecutivesSelected()
                    if (search_executive !== -1) {

                        let element = this.executives_selected[search_executive];
                        element.region_id = this.$route.params.region_id;

                        API({
                            method: 'post',
                            url: 'client_executive/inverse',
                            data: element
                        })
                            .then((result) => {
                                if (result.data.success === true) {
                                    this.getExecutives()
                                    this.getExecutivesSelected()
                                    this.loading = false
                                }
                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                            })
                        })
                    } else {
                        if (this.executives_selected.length > 0) {
                            this.loading = true
                            let element = this.executives_selected.shift()
                            element.region_id = this.$route.params.region_id;

                            API({
                                method: 'post',
                                url: 'client_executive/inverse',
                                data: element
                            })
                                .then((result) => {
                                    if (result.data.success === true) {
                                        this.executives.push(element)
                                        this.loading = false
                                    }
                                }).catch((e) => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                    text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                                })
                            })
                        }
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            moveAllExecutives: function () {
                if (this.loading === false) {

                    this.loading = true

                    if (this.executives.length > 0) {
                        for (let i = 0; i < this.executives.length; i++) {
                            this.$set(this.executives[i], 'selected', false)
                            this.executives_selected.push(this.executives[i])
                        }
                        this.executives = []

                        API({
                            method: 'post',
                            url: 'client_executive/store/all',
                            data: {
                                client_id: this.$route.params.client_id
                            }
                        })
                            .then((result) => {
                                if (result.data.success === true) {

                                    this.getExecutivesSelected()
                                    this.loading = false
                                }
                            }).catch((e) => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                            })
                        })
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            inverseAllExecutives: function () {
                if (this.loading === false) {
                    this.loading = true
                    if (this.executives_selected.length > 0) {
                        for (let i = 0; i < this.executives_selected.length; i++) {

                            this.executives.push(this.executives_selected[i])
                        }
                        this.executives_selected = []
                        API({
                            method: 'post',
                            url: 'client_executive/inverse/all',
                            data: {
                                client_id: this.$route.params.client_id
                            }
                        })
                            .then((result) => {
                                if (result.data.success === true) {
                                    this.getExecutives()
                                    this.loading = false
                                }
                            }).catch((e) => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                                text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                            })
                        })
                    }
                } else {
                    console.log('Bloqueado accion')
                }
            },
            calculateNumPages: function (num_executives, limit) {
                this.num_pages = Math.ceil(num_executives / limit)
            },
            calculateNumPagesExecutivesSelected: function (num_executives, limit) {
                this.num_pages_executives_selected = Math.ceil(num_executives / limit)
            },
            getScrollTop: function () {
                if (!this.$refs.executivesList) return;

                let scroll = this.$refs.executivesList.scrollTop;

                if (!scroll) {
                    // console.error('Elemento list_services_selected no encontrado');
                    return;
                }

                if (scroll > this.scroll_limit) {
                    this.page += 1
                    this.scroll_limit = 2900 * this.page
                    if (this.page === this.num_pages) {
                        clearInterval(this.interval)
                        this.getExecutivesScroll()
                    } else {

                        this.getExecutivesScroll()
                    }

                }
            },
            getScrollTopExecutivesSelected: function () {
                if (!this.$refs.executivesListSelected) return;

                let scroll = this.$refs.executivesListSelected.scrollTop;

                if (!scroll) {
                    // console.error('Elemento list_services_selected no encontrado');
                    return;
                }

                if (scroll > this.scroll_limit_executives_selected) {
                    this.page_executives_selected += 1
                    this.scroll_limit_executives_selected = 2900 * this.page_executives_selected
                    if (this.page_executives_selected === this.num_pages_executives_selected) {
                        clearInterval(this.interval_executives_selected)
                        this.getExecutivesScrollSelected()
                    } else {

                        this.getExecutivesScrollSelected()
                    }

                }
            },
            getMarkets: function () {
                //markets
                API.get('/markets/selectbox?lang=' + localStorage.getItem('lang'))
                    .then((result) => {

                        let mark = result.data.data
                        mark.forEach((market) => {
                            this.markets.push({
                                label: market.text,
                                code: market.value
                            })
                        })

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                        text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                    })
                })
            },

            getExecutives: function () {

                API({
                    method: 'post',
                    url: 'clients/executive',
                    data: {
                        page: 1,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        market: this.selectMarket,
                        region_id: this.$route.params.region_id,
                    }
                })
                    .then((result) => {
                        let executives_ = []
                        this.executives_selected.forEach( (e_)=>{
                            executives_[e_.user_id] = true
                        })

                        this.executives = []
                        result.data.data.forEach( (data_e)=>{
                            if( !(executives_[data_e.user_id]) ){
                                this.executives.push(data_e)
                            }
                        })

                        this.count = result.data.count

                        this.calculateNumPages(this.count, this.limit)
                        this.scroll_limit = 2900
                        this.$refs.executivesList.scrollTop = 0

                    }).catch((e) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                        text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                    })
                })

            },
            getExecutivesScroll: function () {

                API({
                    method: 'post',
                    url: 'user/search/executive',
                    data: {
                        page: this.page,
                        limit: this.limit,
                        query: this.query,
                        client_id: this.$route.params.client_id,
                        region_id: this.$route.params.region_id,
                    }
                })
                    .then((result) => {
                        let executives = result.data.data
                        for (let i = 0; i < executives.length; i++) {
                            this.executives.push(executives[i])
                        }
                        if (this.page === 1) {
                            this.count = result.data.count
                            this.calculateNumPages(result.data.count, this.limit)
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                        text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                    })
                })

            },
            getExecutivesSelected: function () {
                API({
                    method: 'post',
                    url: 'client_executive',
                    data: {
                        page: 1,
                        limit: this.limit_executives_selected,
                        query: this.query_executives_selected,
                        client_id: this.$route.params.client_id,
                        region_id: this.$route.params.region_id,
                    }
                })
                    .then((result) => {
                        this.executives_selected = result.data.data

                        this.count_executives_selected = result.data.count
                        this.calculateNumPagesExecutivesSelected(result.data.count, this.limit_executives_selected)
                        this.scroll_limit_executives_selected = 2900
                        this.$refs.executivesListSelected.scrollTop = 0

                    }).catch((e) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                        text: this.$t('clientsmanageclientexecutive.error.messages.connection_error') + e
                    })
                })

            },
            getexecutivesScrollSelected: function () {
                API({
                    method: 'post',
                    url: 'client_executive',
                    data: {
                        page: this.page_executives_selected,
                        limit: this.limit_executives_selected,
                        query: this.query_executives_selected,
                        client_id: this.$route.params.client_id,
                        period: this.selectPeriod,
                        region_id: this.$route.params.region_id,
                    }
                })
                    .then((result) => {
                        let executives_selected = result.data.data
                        for (let i = 0; i < executives_selected.length; i++) {
                            this.executives_selected.push(executives_selected[i])
                        }
                        if (this.page === 1) {
                            this.count = result.data.count
                            this.calculateNumPagesHotelsSelected(result.data.count, this.limit)
                        }
                    }).catch((error) => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('clientsmanageclientexecutive.error.messages.name'),
                        text: this.$t('clientsmanageclientexecutive.error.messages.connection_error')
                    })
                })

            },
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

    .style_list_ul_executive {
        height: 500px;
        max-height: 500px;
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

    .i-error{
        color: #ff7549;
    }
    .i-success{
        color: #21b454;
    }

</style>


