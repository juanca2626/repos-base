<template>
    <div class="background-secondary" v-show="show_recommended">
        <section class="container">
            <h1 class="sub-title">
                <b-skeleton-wrapper :loading="loading_recommended">
                    <template #loading>
                        <b-skeleton width="100%" height="10%"></b-skeleton>
                    </template>
                    <span v-if="package_recommended.length > 0">{{translations.label.recommendations_of_the_month}}</span>
                </b-skeleton-wrapper>
            </h1>
            <b-skeleton-wrapper :loading="loading_recommended">
                <template #loading>
                    <b-row>
                        <b-col cols="4" class="mt-3 mb-4">
                            <b-skeleton-img></b-skeleton-img>
                            <b-skeleton class="mt-3" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="50%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" height="20%"></b-skeleton>
                        </b-col>
                        <b-col cols="4" class="mt-3 mb-4">
                            <b-skeleton-img></b-skeleton-img>
                            <b-skeleton class="mt-3" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="50%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" height="20%"></b-skeleton>
                        </b-col>
                        <b-col cols="4" class="mt-3 mb-4">
                            <b-skeleton-img></b-skeleton-img>
                            <b-skeleton class="mt-3" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="60%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" width="50%" height="5%"></b-skeleton>
                            <b-skeleton class="mt-3" height="20%"></b-skeleton>
                        </b-col>
                    </b-row>
                </template>

                <div class="content-cards">
                    <div class="grid">
                        <div class="grid__item" v-for="recommended in package_recommended">
                            <div class="card">
                                <img class="card__img"
                                     @click="goToPackageDetails(recommended)"
                                     :src="recommended.galleries[0].url"
                                     alt="Snowy Mountains"/>
                                <div class="card__content">
                                    <div class="d-flex justify-content-between">
                                        <h2 class="card__header">{{ recommended.descriptions.name }}</h2>
                                        <div class="card__days">{{ recommended.nights + 1 }}D/{{ recommended.nights
                                            }}N
                                        </div>
                                    </div>
                                    <div class="card__tag gastronomia"
                                         :style="'background-color:#' + recommended.tag.color +' '">{{
                                        recommended.tag.name
                                        }}
                                    </div>
                                    <div class="card__ubi"><span class="icon-map-pin mr-1"></span> {{
                                        recommended.destinations.destinations_display }}
                                    </div>
                                    <p class="card__text">{{ recommended.descriptions.description |
                                        truncate(200,'...')}}</p>
                                    <div class="card__price d-flex justify-content-between">
                                        <button class="card__btn" @click="goToPackageDetails(recommended)">
                                            {{ translations.label.see_more }}<span>&rarr;</span>
                                        </button>
                                        <div class="text-right price">
                                            <span style="font-size: 12px"
                                                v-if="client && client.commission_status == 1 && parseFloat(client.commission) > 0 && user_type_id == 4"
                                                class="badge badge-warning ml-2">
                                                {{  translations.label.with_commission }}
                                            </span>
                                            <div class="price__value">{{translations.label.from}} <span class="icon-dollar-sign"></span> {{
                                                getPrice(recommended.amounts.price_per_adult.room_dbl) }}
                                            </div>
                                            <span class="price__text">{{ translations.label.per_passenger }}</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </b-skeleton-wrapper>
        </section>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
                date: moment().add(1, 'days').format('DD/MM/YYYY'),
                loading_recommended: false,
                show_recommended: true,
                package_recommended: [],
                lang: 'en',
                client_id: '',
                rooms: {
                    quantity_sgl: 0,
                    quantity_dbl: 1,
                    quantity_tpl: 0,
                },
                quantity_persons: {
                    adults: 2,
                    child_with_bed: 0,
                    child_without_bed: 0,
                    age_child: [
                        {
                            age: 1,
                        },
                    ],
                },
                rooms: {
                    quantity_sgl: 0,
                    quantity_dbl: 1,
                    quantity_tpl: 0
                },
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                client: {},
                user_type_id: '',
            }
        },
        created () {
            this.client_id = localStorage.getItem('client_id')
            this.lang = localStorage.getItem('lang')
            this.user_type_id = localStorage.getItem('user_type_id');
            if (this.client_id) {
                this.getClient();
            }
        },
        mounted () {
            this.setTranslations()
            this.getPackagesRecommended()
        },
        methods: {
            setTranslations() {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                    this.translations = data.data
                })
            },
            getPackagesRecommended () {
                console.log('client_id '+ this.client_id)
                if (this.client_id) {
                    this.loading_recommended = true
                    let data = {
                        lang: localStorage.getItem('lang'),
                        client_id: this.client_id,
                        type_service: 'all',
                        limit: 3,
                        date: (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.quantity_persons,
                        only_recommended: 1,
                        rooms: this.rooms,
                    }
                    axios.post(
                        baseExternalURL + 'services/client/packages',
                        data,
                    ).then((result) => {
                        this.loading_recommended = false
                        if (result.data.success) {
                            this.package_recommended = result.data.data
                            if (this.package_recommended.length === 0) {
                                this.show_recommended = false
                            }
                        }
                    }).catch((e) => {
                        this.loading_recommended = false
                        console.log(e)
                    })
                }
            },
            goToPackageDetails (pack) {
                let data = {
                    lang: localStorage.getItem('lang'),
                    date: moment().add(1, 'days').format('YYYY-MM-DD'),
                    quantity_persons: this.quantity_persons,
                    type_service: pack.type_services[0].id,
                    rooms: this.rooms,
                    date_to_days: pack.nights + 1,
                    package_ids: [pack.id]
                }
                localStorage.setItem('parameters_packages_details', JSON.stringify(data))
                window.location = baseURL + 'package-details'
            },
            getClient() {
                    axios.get(`${baseExternalURL}api/clients/${this.client_id}`)
                        .then((response) => {
                            this.client = response.data.data;
                        })
                        .catch((error) => {
                            console.error("Error al obtener el cliente:", error);
                        });
            },
            getPrice(price) {

                    if (
                        this.client &&
                        this.client.commission_status == 1 &&
                        parseFloat(this.client.commission) > 0 &&
                        this.user_type_id == 4
                    ) {
                        let commissionRate = parseFloat(this.client.commission) / 100;
                        let priceWithCommission = price * (1 + commissionRate);

                        // Usar roundLito para redondear
                        let rounded = this.roundLito(priceWithCommission);

                        return rounded;
                    }

                    return this.roundLito(price);
                },
                roundLito: function(num) {
                    num = parseFloat(num);
                    num = (num).toFixed(2);

                    if (num != null) {
                        var res = String(num).split(".");
                        var nEntero = parseInt(res[0]);
                        var nDecimal = 0;
                        if (res.length > 1)
                            nDecimal = parseInt(res[1]);

                        var newDecimal;
                        if (nDecimal < 25) {
                            newDecimal = 0;
                        } else if (nDecimal >= 25 && nDecimal < 75) {
                            newDecimal = 5;
                        } else if (nDecimal >= 75) {
                            nEntero = nEntero + 1;
                            newDecimal = 0;
                        }

                        return parseFloat(String(nEntero) + "." + String(newDecimal));
                    }
                },
        }
    }
</script>

<style scoped>

</style>
