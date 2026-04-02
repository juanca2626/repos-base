<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
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
                items: [
                    {
                        id: 1,
                        title: 'title2',
                        link: '/services/offer/list',
                        status: ''
                    },
                ]
            }
        },
        created: function () {
            this.items = [
                {
                    id: 1,
                    title: 'title2',
                    link: `/manage_client/regions/${this.$route.params.region_id}/services/offer/list`,
                    status: '',
                    activeRoutes: ['ServiceOffer']
                },
            ];

            this.items.forEach(item => {
                item.status = item.activeRoutes.includes(this.$route.name) ? 'active' : '';
            });
            // if (this.$route.name === 'CrossSelling') {
            //   this.items[1].status = 'active'
            // }
        },
        methods: {
            tabsStatus: function (link, id) {
                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id == this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push('/clients/' + this.$route.params.client_id + link)
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

<i18n src="./services.json"></i18n>
