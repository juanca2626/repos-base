<template>
    <div class="w-100">
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
                        title: 'Asociar',
                        link: '/manage_service/service_supplements',
                        icon: 'dot-circle',
                        status: ''
                    },
                    // {
                    //   id: 2,
                    //   title: 'Calendario',
                    //   link: '/manage_service/supplements_hotel/calendary',
                    //   status: ''
                    // }
                ]
            }
        },
        created: function () {
            if (this.$route.name === 'ServiceSupplementList') {
                this.items[0].status = 'active'
            }

        },
        methods: {
            tabsStatus: function (link, id) {
                for (let i = this.items.length - 1; i >= 0; i--) {
                    if (id === this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push('/services_new/' + this.$route.params.service_id + link)
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
