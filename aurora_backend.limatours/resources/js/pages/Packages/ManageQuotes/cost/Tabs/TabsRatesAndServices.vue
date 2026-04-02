<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status ==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
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
                category_id: '',
                items: [
                    {
                        id: 1,
                        title: 'Servicios & Hoteles',
                        link: '',
                        icon: 'dot-circle',
                        status: ''
                    },
                    {
                        id: 2,
                        title: 'Tarifas',
                        link: '',
                        icon: 'dot-circle',
                        status: ''
                    },
                    // {
                    //     id: 3,
                    //     title: 'Opcionales',
                    //     link: '',
                    //     icon: 'dot-circle',
                    //     status: ''
                    // },

                ]
            }
        },
        mounted () {
            this.getCategories()
        },
        created: function () {
            if (this.$route.name === 'PackageCostQuoteServicesAndHotels') {
                this.items[0].status = 'active'
            }
            if (this.$route.name === 'PackageCostQuoteRates') {
                this.items[1].status = 'active'
            }
            // if (this.$route.name === 'PackageCostQuoteOptional') {
            //     this.items[2].status = 'active'
            // }
            this.category_id = this.$route.params.category_id
            this.$root.$on('updateCategory', (payload) => {
                this.category_id = payload.categoryId
                this.getCategories()
                this.items[0].status = 'active'
                this.items[1].status = ''
            })
            this.getCategories()
        },
        methods: {
            getCategories: function () {
                this.items[0].link = '/quotes/cost/' + this.$route.params.package_plan_rate_id + '/category/' + this.category_id + '/services'
                this.items[1].link = '/quotes/cost/' + this.$route.params.package_plan_rate_id + '/category/' + this.category_id + '/rates'
                // this.items[2].link = '/quotes/cost/' + this.$route.params.package_plan_rate_id + '/category/' + this.category_id + '/optional'
                // this.tabsStatus(items[0].link,1)
            },
            tabsStatus: function (link, id) {
                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id === this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push('/packages/' + this.$route.params.package_id + link)
            }
        }
    }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

</style>
