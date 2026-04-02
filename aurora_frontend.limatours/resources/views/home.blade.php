@extends('layouts.app')
@section('content')
    <section>
        <div class="hero__primary">
            <h1>{{ trans('global.label.welcome_to_aurora') }}</h1>
            <p class="pb-4">{{ trans('global.label.choose_the_perfect_experience') }}</p>
            <div class="searcher">
                <div class="d-flex justify-content-end align-items-center mb-3"
                     style="color: #343a40;font-weight: 500;">
                    <div class="mr-3"><i class="fas fa-filter" style="font-size: 2.2rem; color: #eb5757;"></i></div>
                    <div class="filter__tabs" data-filter-group="category">
                        <button class="filter__btn" v-bind:class="{'is-checked': (type_package_all === 0) }"
                                :disabled="loading_packages"
                                @click="searchByType('all')">
                            <i class="fas fa-spinner fa-spin" v-show="loading_packages"></i>
                            <i class="fas fa-check" v-show="(type_package_all === 0) && !loading_packages"></i>
                            {{trans('package.label.see_everything')}}
                        </button>
                        <button class="filter__btn" v-bind:class="{'is-checked': (type_package_all === 2)}"
                                :disabled="loading_packages"
                                @click="searchByType('exclusive')">
                            <i class="fas fa-spinner fa-spin" v-show="loading_packages"></i>
                            <i class="fas fa-check" v-show="(type_package_all === 2) && !loading_packages"></i>
                            {{trans('package.label.see_exclusives')}}
                        </button>
                        <button class="filter__btn" v-bind:class="{'is-checked': (type_package_all === 1)}"
                                :disabled="loading_packages"
                                @click="searchByType('extension')">
                            <i class="fas fa-spinner fa-spin" v-show="loading_packages"></i>
                            <i class="fas fa-check" v-show="(type_package_all === 1) && !loading_packages"></i>
                            {{trans('package.label.see_extensions')}}
                        </button>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="input-group" style="width: 42%;">
                        <b-form-select v-model="selectedDay" class="input-number" :options="days" value-field="code"
                                       text-field="label" name="days" id="days">
                        </b-form-select>
                    </div>
                    <div class="form-control dropdown destination">
                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuDestinies"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="d-flex justify-content-between">
                                <span class="text-left" v-if="selectedDestinationsLabel === ''">{{ trans('global.label.destinations') }}</span>
                                <span class="text-left" v-else>
                                <span v-if="selectedDestinations.length > 1">
                                 @{{ selectedDestinations.length }} seleccionados
                                </span>
                                <span v-else>
                                @{{ selectedDestinationsLabel }}
                                </span>
                            </span>
                                <i class="fas fa-sort-down"></i>
                            </div>

                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuDestinies">
                            <div class="d-flex align-items-center dropdown-menu__option"
                                 v-for="destiny in destinations">
                                <label class="checkbox-ui" @click="selectedDestiny(destiny)">
                                    <i :class="{'fa fa-check-square' : destiny.active, 'far fa-square':!(destiny.active)}"></i>
                                    @{{ destiny.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- interest --}}
                    <div class="form-control dropdown interest">
                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuInterests"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="d-flex justify-content-between">
                            <span class="text-left" v-if="selectedInterestsLabel === ''">
                                @{{ translate_global.label.interests }}
                            </span>
                                <span class="text-left" v-else>
                                <span v-if="selectedInterests.length > 1">
                                     @{{ selectedInterests.length }} seleccionados
                                </span>
                                <span v-else>
                                    @{{ selectedInterestsLabel }}
                                </span>
                            </span>
                                <i class="fas fa-sort-down"></i>
                            </div>

                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuInterests">
                            <div class="d-flex align-items-center dropdown-menu__option" v-for="interest in interests">
                                <label class="checkbox-ui" @click="selectedInterest(interest)">
                                    <i :class="{'fa fa-check-square' : interest.active, 'far fa-square':!(interest.active)}"></i>
                                    @{{ interest.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- categories --}}
                    <div class="form-control dropdown interest" style="width: 75%">
                        <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuCategories"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="d-flex justify-content-between">
                            <span class="text-left" v-if="selectedCategoriesLabel === ''">
                                @{{ translate_global.label.categories }}
                            </span>
                                <span class="text-left" v-else>
                                <span v-if="selectedCategories.length > 1">
                                     @{{ selectedCategories.length }} seleccionados
                                </span>
                                <span v-else>
                                    @{{ selectedCategoriesLabel }}
                                </span>
                            </span>
                                <i class="fas fa-sort-down"></i>
                            </div>

                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuCategories">
                            <div class="d-flex align-items-center dropdown-menu__option" v-for="category in filteredCategories">
                                <label class="checkbox-ui" @click="selectedCategory(category)">
                                    <i :class="{'fa fa-check-square' : category.active, 'far fa-square':!(category.active)}"></i>
                                    @{{ category.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="search">
                        <input type="text" class="search-term" v-model="filter"
                               placeholder="¿ {{ trans('global.label.search') }} ?">
                        <button type="button" class="search-button__delete" @click="cleanFilters()">
                            <span class="icon-ac-x-circle"></span>
                        </button>
                        <button type="submit" class="search-button" @click="goToPackages">
                            <span class="icon-search"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="container">
        <div class="col-md-12 text-center p-0 mt-4">
            <b-form-group>
                <b-form-radio-group
                    button-variant="outline-danger"
                    id="radio-group-1"
                    v-model="selected_year"
                    :options="year_options"
                    name="radio-options"
                    @input="search"
                    size="lg"
                    buttons
                    :disabled="loading_packages"
                ></b-form-radio-group>
            </b-form-group>
        </div>
        <b-skeleton-wrapper :loading="loading_packages">
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
                    <div class="grid__item" v-for="best_seller in packages">
                        <div class="card">
                            <img class="card__img"
                                 @click="goToPackageDetails(best_seller)"
                                 :src="best_seller.galleries[0].url"
                                 alt="Snowy Mountains"/>
                            <div class="card__content">
                                <div class="d-flex justify-content-between">
                                    <h2 class="card__header">@{{ best_seller.descriptions.name }}</h2>
                                    <div class="card__days">@{{ best_seller.nights + 1 }}D/@{{ best_seller.nights }}N
                                    </div>
                                </div>
                                <div class="card__tag gastronomia"
                                     :style="'background-color:#' + best_seller.tag.color +' '">@{{ best_seller.tag.name
                                    }}
                                </div>
                                <div class="card__ubi"><span class="icon-map-pin mr-1"></span> @{{
                                    best_seller.destinations.destinations_display }}
                                </div>
                                <p class="card__text">@{{ best_seller.descriptions.description | truncate(200,'...')
                                    }}</p>
                                <div class="card__price d-flex justify-content-between">
                                    <button class="card__btn" @click="goToPackageDetails(best_seller)">@{{
                                        translate_global.label.see_more }}<span>&rarr;</span>
                                    </button>
                                    <div class="text-right price">
                                        <span v-if="userTypeIsClient && commission_status === 1" class="price__text" style="color: red">
                                             @{{ translate_global.label.with_commission }}
                                        </span>
                                        <div class="price__value">@{{ translate_global.label.from }} <span
                                                class="icon-dollar-sign"></span> @{{
                                             totalAmountWithCommission(best_seller.amounts.price_per_adult.room_dbl) }}
                                        </div>
                                        <span class="price__text">@{{ translate_global.label.per_passenger }}</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </b-skeleton-wrapper>
    </section>
    <package-best-seller></package-best-seller>
    <package-recommended></package-recommended>
    <aurora-you-can-component></aurora-you-can-component>
    <section-write-us-component></section-write-us-component>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                loading_packages: false,
                date: moment().add(1, 'days').format('DD/MM/YYYY'),
                lang: 'en',
                selectedDay: null,
                selectedDestinations: [],
                selectedDestinationsLabel: '',
                selectedCategoriesLabel: '',
                selectedCategories: [],
                selectedInterestsLabel: '',
                selectedInterests: [],
                destinations: [],
                interests: [],
                categories: [],
                days: [],
                filter: '',
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                translate_global: {
                    label: {},
                    validations: {},
                    messages: {}
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
                type_package_all: 0,
                type_package: [0, 1, 2],
                year_options: [
                    {text: 2026, value: 2026},
                    {text: 2027, value: 2027},
                ],
                selected_year: 2026,
                packages: [],
                commission_status: 0,
                commission_percentage: 0,

            },
            created: function () {
                this.client_id = localStorage.getItem('client_id')
                this.lang = localStorage.getItem('lang')
                document.cookie = window.parametersPackagesDetails + "=;domain=" + window.domain + ";";
            },
            mounted () {
                this.setTranslationsGlobal()
                this.setTranslations()
                this.search()
            },
            watch: {
                selectedInterests: function(interests) {
                    this.selectedCategories = this.selectedCategories.filter(categoryCode => interests.includes(categoryCode));
                },
                selectedCategories: function(categories) {
                     if (categories.length === 0) {
                        this.categories = this.categories.map(category => ({
                            ...category,
                            active: false,
                        }));
                        this.selectedCategoriesLabel = '';
                    }
                }
            },
            computed: {
                filteredCategories: function() {
                    console.log(this.categories)
                    if (this.selectedInterests.length === 0) {
                        return this.categories;
                    }
                    console.log(this.categories.filter(category => this.selectedInterests.includes(category.group_code)))
                    return this.categories.filter(category => this.selectedInterests.includes(category.group_code));
                },
                userTypeIsClient() {
                    return localStorage.getItem("user_type_id") === "4";
                }
            },
            methods: {
                search() {
                    localStorage.setItem('packages_year', this.selected_year)
                    if (this.client_id) {
                        let date_new = ''
                        if (this.selected_year === 2026 || this.selected_year === 2027) {
                            date_new = '02/01/' + this.selected_year
                            this.date = date_new
                            date_new = moment(date_new, 'DD/MM/YYYY').format('YYYY-MM-DD')
                        } else {
                            date_new = moment().add(1, 'days').format('DD/MM/YYYY')
                            this.date = moment().add(1, 'days').format('DD/MM/YYYY')
                            date_new = (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD')
                        }
                        this.loading_packages = true
                        this.show_packages = true
                        let data = {
                            lang: localStorage.getItem('lang'),
                            client_id: this.client_id,
                            type_service: 'all',
                            destinations: this.selectedDestinations,
                            groups: this.selectedInterests,
                            tags: this.selectedCategories,
                            date: date_new,
                            quantity_persons: this.quantity_persons,
                            filter: this.filter,
                            rooms: {
                                quantity_sgl: 0,
                                quantity_dbl: 1,
                                quantity_tpl: 0,
                            },
                            days: this.selectedDay,
                            type_package: this.type_package
                        }
                        this.storageParameterLocal()
                        axios.post(
                            baseExternalURL + 'services/client/packages',
                            data,
                        ).then((result) => {
                            this.loading_packages = false
                            if (result.data.success) {
                                this.packages = result.data.data
                                this.commission_percentage = result.data.commission;
                                this.commission_status = result.data.commission_status;
                                if (this.packages.length === 0) {
                                    this.show_packages = false
                                }
                            }
                        }).catch((e) => {
                            this.loading_packages = false
                            console.log(e)
                        })
                    }
                },
                storageParameterLocal() {
                    let search_packages = {
                        days: this.selectedDay,
                        destinations: this.selectedDestinations,
                        interests: this.selectedInterests,
                        categories: this.selectedCategories,
                        filter: this.filter
                    }
                    localStorage.setItem('search_parameters_packages', JSON.stringify(search_packages))
                },
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                        this.translations = data.data
                        this.getParameters()
                    })
                },
                goBiosafetyProtocols () {
                    window.location.href = '/biosafety-protocols'
                },
                setTranslationsGlobal() {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                        this.translate_global = data.data
                    })
                },
                getParameters () {
                    this.loading_packages = true
                    axios.get(baseExternalURL + 'services/clients/parameter_search?client_id=' + this.client_id + '&lang=' + localStorage.getItem('lang')).then((result) => {
                            this.loading_packages = false
                            if (result.data.success) {
                                let data = result.data.data
                                let destinations = []
                                let interests = []
                                let days = []
                                let categories = [];
                                data.destinations.forEach(function (value) {
                                    destinations.push({
                                        'code': value.code,
                                        'label': value.label,
                                        'active': false,
                                    })
                                })
                                this.destinations = destinations
                                data.interests.forEach(function (value) {
                                    categories.push({
                                        'code': value.code,
                                        'label': value.label,
                                        'group_code': value.group_code,
                                        'active': false,
                                    })
                                })
                                this.categories = categories
                                days.push({
                                    'code': null,
                                    'label': this.translations.label.days,
                                })
                                data.days.forEach(function (value) {
                                    days.push({
                                        'code': value.day,
                                        'label': value.day,
                                    })
                                })
                                this.days = days
                                data.groups.forEach((value) => interests.push({
                                    code: value.code,
                                    label: value.label,
                                    active: false,
                                }));
                                this.interests = interests;
                                // Destinos seleccionados
                                if (this.selectedDestinations.length > 0) {
                                    let destinationsNames = []
                                    for (let i = 0; i < this.destinations.length; i++) {
                                        for (let d = 0; d < this.selectedDestinations.length; d++) {
                                            if (this.destinations[i].code === this.selectedDestinations[d]) {
                                                this.destinations[i].active = true
                                                destinationsNames.push(this.destinations[i].label)
                                            }
                                        }
                                    }
                                    this.selectedDestinationsLabel = destinationsNames.join(', ')
                                }
                                if (this.selectedInterests.length > 0) {
                                    let selectedInterestsNames = []
                                    for (let i = 0; i < this.interests.length; i++) {
                                        for (let d = 0; d < this.selectedInterests.length; d++) {
                                            if (this.interests[i].code === this.selectedInterests[d]) {
                                                this.interests[i].active = true
                                                selectedInterestsNames.push(this.interests[i].label)
                                            }
                                        }
                                    }
                                    this.selectedInterestsLabel = selectedInterestsNames.join(', ')
                                }

                                //Intereses seleccionados
                                if (this.selectedCategories.length > 0) {
                                    let selectedCategoriesNames = []
                                    for (let i = 0; i < this.categories.length; i++) {
                                        for (let d = 0; d < this.selectedCategories.length; d++) {
                                            if (this.categories[i].code === this.selectedCategories[d]) {
                                                this.categories[i].active = true
                                                selectedCategoriesNames.push(this.categories[i].label)
                                            }
                                        }
                                    }
                                    this.selectedCategoriesLabel = selectedCategoriesNames.join(', ')
                                }
                            }
                        },
                    ).catch((e) => {
                        this.loading_packages = false
                        console.log(e)
                    })
                },
                selectedDestiny (destiny) {
                    let destinationsCodes = []
                    let destinationsNames = []
                    destiny.active = !(destiny.active)
                    for (let i = 0; i < this.destinations.length; i++) {
                        if (this.destinations[i].active) {
                            destinationsCodes.push(this.destinations[i].code)
                            destinationsNames.push(this.destinations[i].label)
                        }
                    }
                    this.selectedDestinationsLabel = destinationsNames.join(', ')
                    this.selectedDestinations = destinationsCodes

                },
                selectedInterest (interest) {
                    let selectedInterestsCodes = []
                    let selectedInterestsNames = []
                    interest.active = !(interest.active)
                    for (let i = 0; i < this.interests.length; i++) {
                        if (this.interests[i].active) {
                            selectedInterestsCodes.push(this.interests[i].code)
                            selectedInterestsNames.push(this.interests[i].label)
                        }
                    }
                    this.selectedInterestsLabel = selectedInterestsNames.join(', ')
                    this.selectedInterests = selectedInterestsCodes
                },
                selectedCategory(category) {
                    let selectedCategoriesCodes = []
                    let selectedCategoriesNames = []
                    category.active = !(category.active)
                    for (let i = 0; i < this.categories.length; i++) {
                        if (this.categories[i].active) {
                            selectedCategoriesCodes.push(this.categories[i].code)
                            selectedCategoriesNames.push(this.categories[i].label)
                        }
                    }
                    this.selectedCategoriesLabel = selectedCategoriesNames.join(', ')
                    this.selectedCategories = selectedCategoriesCodes
                },
                goToContactUs () {
                    window.location.href = '/contact-us'
                },
                goToPackages () {
                    localStorage.removeItem('search_parameters_packages')
                    let search_packages = {
                        days: this.selectedDay,
                        destinations: this.selectedDestinations,
                        interests: this.selectedInterests,
                        filter: this.filter,
                        type_package_all: this.type_package_all,
                        categories: this.selectedCategories,
                    }
                    localStorage.setItem('search_parameters_packages', JSON.stringify(search_packages))
                    window.location.href = '/packages'
                },
                cleanFilters () {
                    this.selectedDay = null
                    this.selectedDestinations = []
                    this.selectedInterests = []
                    this.selectedDestinationsLabel = ''
                    this.selectedInterestsLabel = ''
                    this.filter = ''
                    for (let i = 0; i < this.interests.length; i++) {
                        this.interests[i].active = false
                    }
                    for (let i = 0; i < this.destinations.length; i++) {
                        this.destinations[i].active = false
                    }
                },
                goToPackageDetails (pack) {
                    let date_new = ''
                    if (this.selected_year === 2026 || this.selected_year === 2027) {
                        date_new = '02/01/' + this.selected_year
                        this.date = date_new
                        date_new = moment(date_new, 'DD/MM/YYYY').format('YYYY-MM-DD')
                    } else {
                        date_new = moment().add(1, 'days').format('DD/MM/YYYY')
                        this.date = moment().add(1, 'days').format('DD/MM/YYYY')
                        date_new = (this.date === '') ? moment().format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD')
                    }
                    let data = {
                        lang: localStorage.getItem('lang'),
                        date: date_new,
                        quantity_persons: this.quantity_persons,
                        type_service: 1,
                        date_to_days: pack.nights + 1,
                        package_ids: [pack.id]
                    }
                    localStorage.setItem('parameters_packages_details', JSON.stringify(data))
                    window.location = baseURL + 'package-details'
                },
                searchByType (type) {
                    if (type === 'all') {
                        this.type_package_all = 0
                        this.type_package = [0, 1, 2]
                        this.search()
                    } else if (type === 'exclusive') {
                        this.type_package_all = 2
                        this.type_package = [2]
                        this.search()
                    } else {
                        this.type_package_all = 1
                        this.type_package = [1]
                        this.search()
                    }
                },
                totalAmountWithCommission(price) {
                    // Eliminar comas y convertir a float
                    const numericPrice = parseFloat(String(price).replace(/,/g, '')) || 0;

                    // Solo calcula si el status es 1 y hay porcentaje
                    if (this.commission_status === 1 && this.commission_percentage > 0) {
                        const commissionRate = this.commission_percentage / 100;
                        const withCommission = numericPrice * (1 + commissionRate);
                        // Redondear siempre hacia arriba al entero más cercano
                        return Math.ceil(withCommission);
                    }

                    return price;
                }
            }
        })
    </script>
@endsection
