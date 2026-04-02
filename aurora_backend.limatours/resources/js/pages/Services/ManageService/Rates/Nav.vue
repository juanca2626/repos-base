<template>
    <div class="w-100">
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status==='active' && item.show">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-if="item.status==='' && item.show">
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>

<script>

    export default {
        data: () => {
            return {
                hasClient: false
            }
        },
        computed: {
            items: function () {
                return [
                    {
                        id: 1,
                        title: 'Costo',
                        link: '/services_new/' + this.$root.$route.params.service_id + '/manage_service/rates/cost',
                        icon: 'dot-circle',
                        status: '',
                        show: true,
                    },
                    {
                        id: 2,
                        title: 'Venta',
                        link: '/services_new/' + this.$root.$route.params.service_id + '/manage_service/rates/sale',
                        status: '',
                        show: (!this.hasClient),
                    }
                ]
            }
        },
        created: function () {
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
            if (this.$route.name === 'RatesRatesCostList' || this.$route.name === 'SupplementServiceRate') {
                this.items[0].status = 'active'
            }
            if (this.$route.name === 'RatesRatesSale') {
                this.items[1].status = 'active'
            }
        },
        methods: {
            tabsStatus: function (link, id) {
                for (let i = this.items.length - 1; i >= 0; i--) {
                    if (id === this.items[i].id) {
                        console.log(this.items[i])
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push(link)
            }
        }
    }
</script>

<style lang="stylus">
    .s-color
        color red

    .fondo-nav
        background-color #f9fbfc
</style>
