<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active v-show="item._show">
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)" v-show="item._show">
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
                        title: 'Textos',
                        link: '/manage_train/train_texts',
                        icon: 'dot-circle',
                        status: '',
                        _show: true
                    },
                    {
                        id: 2,
                        title: 'Amenities',
                        link: '/manage_train/amenities',
                        icon: 'dot-circle',
                        status: '',
                        _show: true
                    },
                    {
                        id: 3,
                        title: 'Incluyentes',
                        link: '/manage_train/includes',
                        icon: 'dot-circle',
                        status: '',
                        _show: true
                    },
                    {
                        id: 4,
                        title: 'Galería',
                        link: '/manage_train/train_gallery',
                        status: '',
                        _show: true
                    },
                    {
                        id: 5,
                        title: 'Configuración',
                        link: '/manage_train/train_configurations',
                        status: '',
                        _show: true
                    },
                    {
                        id: 6,
                        title: 'Tarifas',
                        link: '/manage_train/rates',
                        status: '',
                        _show: true
                    },
                    {
                        id: 7,
                        title: 'Inventario',
                        link: '/manage_train/inventories',
                        status: '',
                        _show: true
                    }
                ]
            }
        },
        created: function () {
            if (this.$route.name === 'TrainTextsForm') {
                this.items[0].status = 'active'
                this.items[0]._show = true
            }
            if (this.$route.name === 'TrainAmenitiesLayout') {
                this.items[1].status = 'active'
                this.items[1]._show = true
            }
            if (this.$route.name === 'IncludeTrainLayout') {
                this.items[2].status = 'active'
                this.items[2]._show = 'active'
            }
            if (this.$route.name === 'TrainGalleryManageList') {
                this.items[3].status = 'active'
                this.items[3]._show = true
            }
            if (this.$route.name === 'TrainConfigurationsLayout') {
                this.items[4].status = 'active'
                this.items[4]._show = true
            }
            if (this.$route.name === 'RatesList') {
                this.items[5].status = 'active'
                this.items[5]._show = true
            }
            if (this.$route.name === 'AvailabilityServiceLayout') {
                this.items[6].status = 'active'
                this.items[6]._show = true
            }
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
                this.$router.push('/trains/' + this.$route.params.train_id + link)
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


