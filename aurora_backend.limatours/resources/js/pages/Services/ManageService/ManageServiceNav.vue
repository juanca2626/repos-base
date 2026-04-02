<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items" v-if="item.permission">
                <template v-if="item.status == 'active'">
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

    import { API } from './../../../api'
    export default {
        data: () => {
            return {
                items: [
                    {
                        id: 1,
                        title: 'service.schedule',
                        link: '/manage_service/schedule',
                        icon: 'dot-circle',
                        status: '',
                        permission: "manageservices.schedule"
                    },
                    {
                        id: 2,
                        title: 'service.operability',
                        link: '/manage_service/operability',
                        status: '',
                        permission: "manageservices.operability"
                    },
                    {
                        id: 3,
                        title: 'service.gallery',
                        link: '/manage_service/gallery',
                        status: '',
                        permission: "manageservices.gallery"
                    },
                    {
                        id: 4,
                        title: 'servicesmanageservicepolitics.configuration',
                        link: '/manage_service/politics',
                        status: '',
                        permission: "manageservices.configuration"
                    },
                    {
                        id: 5,
                        title: 'service.includes',
                        link: '/manage_service/includes',
                        status: '',
                        permission: "manageservices.includes"
                    },
                    {
                        id: 6,
                        title: 'service.availability',
                        link: '/manage_service/availability',
                        status: '',
                        permission: "manageservices.availability"
                    },
                    {
                        id: 7,
                        title: 'service.rates',
                        link: '/manage_service/rates',
                        status: '',
                        permission: "manageservices.rates"
                    },
                    {
                        id: 8,
                        title: 'Multiservicios',
                        link: '/manage_service/service_components',
                        status: '',
                        permission: "manageservices.multiservices"
                    },
                    {
                        id: 9,
                        title: 'service.equivalences',
                        link: '/manage_service/service_equivalences',
                        status: '',
                        permission: "manageservices.equivalences"
                    },
                    {
                        id: 10,
                        title: 'service.featured',
                        link: '/manage_service/featured',
                        status: '',
                        permission: "manageservices.featured"
                    },
                    {
                        id: 11,
                        title: 'service.instructions',
                        link: '/manage_service/service_instructions',
                        status: '',
                        permission: "manageservices.instructions"
                    },
                    {
                        id: 12,
                        title: 'service.supplements',
                        link: '/manage_service/service_supplements',
                        status: '',
                        permission: "manageservices.supplements"
                    },
                    {
                        id: 13,
                        title: 'Composición',
                        link: '/manage_service/composition',
                        status: '',
                        permission: "manageservices.composition"
                    }
                ],
                permissions : []
            }
        },
        created: function () {
            if (this.$route.name === 'ScheduleServiceLayout') {
                this.items[0].status = 'active'
            }
            if (this.$route.name === 'OperabilityServiceLayout') {
                this.items[1].status = 'active'
            }
            if (this.$route.name === 'GalleryLayout') {
                this.items[2].status = 'active'
            }
            if ((this.$route.name === 'ServicePoliticsLayout') || (this.$route.name === 'PoliticTaxes') || (this.$route.name === 'PoliticConfig')) {
                this.items[3].status = 'active'
            }
            if (this.$route.name === 'IncludeServiceLayout') {
                this.items[4].status = 'active'
            }
            if (this.$route.name === 'AvailabilityServiceLayout') {
                this.items[5].status = 'active'
            }
            if ((this.$route.name === 'RatesRatesLayout') || (this.$route.name === 'RatesRatesCostList') || (this.$route.name === 'RatesRatesSale') || (this.$route.name === 'SupplementServiceRate')) {
                this.items[6].status = 'active'
            }
            if (this.$route.name === 'ServiceComponentsList') {
                this.items[7].status = 'active'
            }
            if (this.$route.name === 'ServiceEquivalenceAssociationsList') {
                this.items[8].status = 'active'
            }
            if (this.$route.name === 'FeaturedServiceLayout') {
                this.items[9].status = 'active'
            }
            if (this.$route.name === 'InstructionsServiceLayout') {
                this.items[10].status = 'active'
            }
            if (this.$route.name === 'ServiceSupplementsLayout' || (this.$route.name === 'ServiceSupplementList')|| (this.$route.name === 'ServiceSupplementAmounts')) {
                this.items[11].status = 'active'
            }
            if (this.$route.name === 'ServiceCompositionLayout' || (this.$route.name === 'ServiceCompositionList')) {
                this.items[12].status = 'active'
            }
            if (this.$route.name === 'ServiceRatesOtsListLayout' || (this.$route.name === 'ServiceRatesOtsList')) {
                this.items[13].status = 'active'
            }
            this.get_permissions()
        },
        methods: {
            tabsStatus: function (link, id) {
                this.$root.$emit('updateTitleService', { tab: 1 })
                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id === this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push('/services_new/' + this.$route.params.service_id + link)
            },
            get_permissions(){
                API({
                    method: 'get',
                    url: 'permissions/name/Manage_Services'
                }).then((result) => {
                    if (result.data.success === true) {
                        this.permissions = result.data.data

                        this.items.forEach( i=>{
                            this.permissions.forEach( p=>{
                                if( p.slug === i.permission ){
                                    i.permission = true
                                }
                            })
                        })

                        this.items.forEach( i=>{
                            if(i.permission!==true ){
                                i.permission = false
                            }
                        })

                    } else {
                        console.log('sin permisos')
                    }
                }).catch((e) => {
                    console.log(e)
                })
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


