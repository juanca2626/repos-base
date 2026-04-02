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
                        title: 'package.texts',
                        link: '/manage_package/package_texts',
                        icon: 'dot-circle',
                        status: '',
                        _show: true
                    },
                    {
                        id: 2,
                        title: 'package.configurations',
                        link: '/manage_package/package_configurations',
                        status: '',
                        _show: true
                    },
                    {
                        id: 3,
                        title: 'package.gallery',
                        link: '/manage_package/package_gallery',
                        status: '',
                        _show: true
                    },
                    {
                        id: 4,
                        title: 'package.outputs',
                        link: '/manage_package/fixed_outputs',
                        status: '',
                        _show: true
                    },
                    {
                        id: 5,
                        title: 'packages.extensions',
                        link: '/manage_package/extensions',
                        status: '',
                        _show: true
                    },
                    {
                        id: 6,
                        title: 'services.inclusions',
                        link: '/manage_package/package_inclusion',
                        status: '',
                        _show: true
                    },
                    {
                        id: 7,
                        title: 'Highlights',
                        link: '/manage_package/package_highlights',
                        status: '',
                        _show: true
                    },

                ]
            }
        },
        created: function () {
            if (this.$route.name === 'PackageTextsForm') {
                this.items[0].status = 'active'
                this.items[0]._show = true
            }
            if (this.$route.name === 'PackageConfigurationsLayout') {
                this.items[1].status = 'active'
                this.items[1]._show = true
            }
            if (this.$route.name === 'PackageGalleryManageList') {
                this.items[2].status = 'active'
                this.items[2]._show = true
            }
            if (this.$route.name === 'FixedOutputsList') {
                this.items[3].status = 'active'
                this.items[3]._show = true
            }
            if (this.$route.name === 'ExtensionsList') {
                this.items[4].status = 'active'
                this.items[4]._show = true
            }
            if (this.$route.name === 'PackageInclusions') {
                this.items[5].status = 'active'
                this.items[5]._show = true
            }
            if (this.$route.name === 'PackageHighlights') {
                this.items[6].status = 'active'
                this.items[6]._show = true
            }
            this.$root.$on('updateTitlePackage', (payload) => {
                this.items[4]._show = (localStorage.getItem('package_extension') == 1) ? false : true
            })
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


