@extends('layouts.app')
@section('content')
<section class="packages">
    <div class="hero__primary">
        <p class="pb-5">@{{ translate_global.label.choose_the_perfect_experience }}</p>
        <div class="container mt-5 mb-5" v-if="client_id === ''">
            <div class="jumbotron bg-danger">
                <h2 class="text-center text-white">
                    <i class="fas fa-exclamation-triangle"></i>{{ trans('package.label.you_must_select_customer')  }}
                    ... <i class="fas fa-hand-point-up"></i>
                </h2>
            </div>
        </div>
        <div class="searcher" v-if="client_id != ''">
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
                <div class="form-control dropdown interest">
                    <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuDestinies"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="text-left text-truncate">
                                @{{ translate_global.label.destinations }}
                                <span v-if="selectedDestinations.length > 0" class="text-muted ml-1" style="font-size: 0.9em;">
                                    (@{{ selectedDestinations.length }})
                                </span>
                            </span>
                            <i class="fas fa-sort-down ml-auto"></i>
                        </div>

                    </a>
                    <div class="dropdown-menu dropdown-menu--grouped" aria-labelledby="dropdownMenuDestinies">
                        <div class="px-3 py-2 sticky-top bg-white border-bottom">
                            <input type="text"
                                class="form-control form-control-sm"
                                v-model="destinationSearch"
                                :placeholder="translate_global.label.search + '...'"
                                @click.stop>
                        </div>
                        <div v-for="(group, country) in groupedDestinations" :key="country" class="destination-group">
                            <div class="destination-group__header" @click="toggleCountry(country)">
                                <i :class="{'fa fa-check-square' : isCountryFullySelected(country),
                                                'fa fa-minus-square' : isCountryPartiallySelected(country),
                                                'far fa-square': !isCountryPartiallySelected(country) && !isCountryFullySelected(country)}"
                                    class="destination-group__checkbox"></i>
                                <span class="destination-group__title">@{{ country }}</span>
                                <span class="destination-group__count">(@{{ getCountrySelectedCount(country) }}/@{{ group.length }})</span>
                            </div>
                            <div class="destination-group__items">
                                <div class="d-flex align-items-center dropdown-menu__option"
                                    v-for="destiny in group" :key="destiny.code">
                                    <label class="checkbox-ui checkbox-ui--nested" @click.stop="selectedDestiny(destiny)">
                                        <i :class="{'fa fa-check-square' : destiny.active, 'far fa-square':!(destiny.active)}"></i>
                                        @{{ destiny.label }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- interest --}}
                <div class="form-control dropdown interest">
                    <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuInterests"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="text-left text-truncate">
                                @{{ translate_global.label.interests }}
                                <span v-if="selectedInterests.length > 0" class="text-muted ml-1" style="font-size: 0.9em;">
                                    (@{{ selectedInterests.length }})
                                </span>
                            </span>
                            <i class="fas fa-sort-down ml-auto"></i>
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
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span class="text-left text-truncate">
                                @{{ translate_global.label.categories }}
                                <span v-if="selectedCategories.length > 0" class="text-muted ml-1" style="font-size: 0.9em;">
                                    (@{{ selectedCategories.length }})
                                </span>
                            </span>
                            <i class="fas fa-sort-down ml-auto"></i>
                        </div>

                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuCategories">
                        <div class="d-flex align-items-center dropdown-menu__option"
                            v-for="category in filteredCategories">
                            <label class="checkbox-ui" @click="selectedCategory(category)">
                                <i :class="{'fa fa-check-square' : category.active, 'far fa-square':!(category.active)}"></i>
                                @{{ category.label }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="search">
                    <input type="text" class="search-term" v-model="filter"
                        :placeholder="'¿ '+translate_global.label.search+' ?'" style="width: 250px">
                    <button type="button" class="search-button__delete" @click="cleanFilters()">
                        <span class="icon-ac-x-circle"></span>
                    </button>
                    <button type="submit" class="search-button" @click="search()" :disabled="!!loading_packages">
                        <span class="icon-search" v-show="!loading_packages"></span>
                        <i class="fas fa-spinner fa-spin" v-show="loading_packages"></i>
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
                :disabled="loading_packages"></b-form-radio-group>
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
                            alt="Snowy Mountains" />
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

<section class="packages__details my-5 container pb-5"
    v-if="packages.length === 0 && !show_packages && client_id != ''">
    <div class="jumbotron">
        <h2 class="text-center"><i class="fas fa-sad-tear"></i>
            ¡{{ trans('package.label.were_sorry_information_found') }}.</h2>
    </div>
</section>

<package-recommended></package-recommended>
<aurora-you-can-component></aurora-you-can-component>
<section-write-us-component></section-write-us-component>
@endsection
@section('js')
<script>
    new Vue({
        el: '#app',
        data: {
            date: moment().add(1, 'days').format('DD/MM/YYYY'),
            lang: 'en',
            loading_packages: false,
            show_packages: true,
            selectedDay: null,
            destinationSearch: '',
            selectedDestinations: [],
            selectedDestinationsLabel: '',
            selectedInterestsLabel: '',
            selectedInterests: [],
            selectedCategoriesLabel: '',
            selectedCategories: [],
            destinations: [],
            interests: [],
            categories: [],
            days: [],
            filter: '',
            sales_from: '',
            client_id: '',
            quantity_persons: {
                adults: 2,
                child_with_bed: 0,
                child_without_bed: 0,
                age_child: [{
                    age: 1,
                }, ],
            },
            packages: [],
            translate_global: {
                label: {},
                validations: {},
                messages: {}
            },
            type_package_all: 0,
            type_package: [0, 1, 2],
            year_options: [{
                text: 2026,
                value: 2026
            },{
                text: 2027,
                value: 2027
            }, ],
            selected_year: 2026,
            commission_status: 0,
            commission_percentage: 0,
        },
        created: function() {
            this.client_id = localStorage.getItem('client_id')
            this.lang = localStorage.getItem('lang')

            this.$root.$on('changeMarkup', function() {
                this.client_id = localStorage.getItem('client_id')
                this.getParameters()
                this.search()
            })

            if (localStorage.getItem('search_parameters_packages')) {
                let parameters = JSON.parse(localStorage.getItem('search_parameters_packages'))
                if (parameters.days) {
                    this.selectedDay = parameters.days
                }
                if (parameters.destinations) {
                    this.selectedDestinations = parameters.destinations
                }
                if (parameters.interests) {
                    this.selectedInterests = parameters.interests
                }
                if (parameters.categories) {
                    this.selectedCategories = parameters.categories
                }
                if (parameters.filter) {
                    this.filter = parameters.filter
                }

                if (parameters.type_package_all === 0) {
                    this.type_package_all = 0
                    this.type_package = [0, 1, 2]
                } else if (parameters.type_package_all === 1) {
                    this.type_package_all = 1
                    this.type_package = [1]
                } else if (parameters.type_package_all === 2) {
                    this.type_package_all = 2
                    this.type_package = [2]
                }
            }

        },
        async mounted() {
            await this.setTranslationsGlobal()
            await this.search()
        },
        watch: {
            selectedInterests: function(interests) {
                // this.selectedCategories = this.selectedCategories.filter(categoryCode => interests.includes(categoryCode));
            },
            // selectedCategories: function (categories) {
            //     if (categories.length === 0) {
            //         this.categories = this.categories.map(category => ({
            //             ...category,
            //             active: false,
            //         }));
            //         this.selectedCategories = [];
            //         this.selectedCategoriesLabel = '';
            //     }
            // }
        },
        computed: {
            userTypeIsClient() {
                return localStorage.getItem("user_type_id") === "4";
            },
            filteredCategories: function() {
                if (this.selectedInterests.length === 0) {
                    return this.categories;
                }

                return this.categories.filter(category => this.selectedInterests.includes(category.group_code));
            },
            groupedDestinations: function() {
                const grouped = {};
                const searchTerm = this.destinationSearch.toLowerCase().trim();

                this.destinations.forEach(destination => {
                    // Filter by search term if it exists
                    if (searchTerm === '' ||
                        destination.label.toLowerCase().includes(searchTerm) ||
                        destination.country.toLowerCase().includes(searchTerm)) {

                        if (!grouped[destination.country]) {
                            grouped[destination.country] = [];
                        }
                        grouped[destination.country].push(destination);
                    }
                });
                // Sort countries: Peru first, then alphabetically
                return Object.keys(grouped).sort((a, b) => {
                    const countryA = a.toUpperCase();
                    const countryB = b.toUpperCase();

                    if (countryA === 'PERU') return -1;
                    if (countryB === 'PERU') return 1;

                    return countryA.localeCompare(countryB);
                }).reduce((acc, key) => {
                    acc[key] = grouped[key];
                    return acc;
                }, {});
            },
            selectedDestinationsSummary: function() {
                const summary = {};
                this.destinations.forEach(dest => {
                    if (dest.active) {
                        if (!summary[dest.country]) {
                            summary[dest.country] = 0;
                        }
                        summary[dest.country]++;
                    }
                });
                return Object.keys(summary).sort().map(country => ({
                    country: country,
                    count: summary[country]
                }));
            }
        },
        methods: {
            scrollTags(direction) {
                const container = this.$refs.tagsContainer;
                const scrollAmount = 100; // Adjust as needed
                if (direction === 'left') {
                    container.scrollLeft -= scrollAmount;
                } else {
                    container.scrollLeft += scrollAmount;
                }
            },
            getParamStorage() {
                if (localStorage.getItem('search_parameters_packages')) {
                    let parameters = JSON.parse(localStorage.getItem('search_parameters_packages'))
                    if (parameters.days) {
                        this.selectedDay = parameters.days
                    }
                    if (parameters.destinations) {
                        this.selectedDestinations = parameters.destinations
                    }
                    if (parameters.interests) {
                        this.selectedInterests = parameters.interests
                    }
                    if (parameters.categories) {
                        this.selectedCategories = parameters.categories
                    }
                    if (parameters.filter) {
                        this.filter = parameters.filter
                    }

                    if (parameters.type_package_all === 0) {
                        this.type_package_all = 0
                        this.type_package = [0, 1, 2]
                    } else if (parameters.type_package_all === 1) {
                        this.type_package_all = 1
                        this.type_package = [1]
                    } else if (parameters.type_package_all === 2) {
                        this.type_package_all = 2
                        this.type_package = [2]
                    }
                }
            },
            goBiosafetyProtocols() {
                window.location.href = '/biosafety-protocols'
            },
            async setTranslationsGlobal() {
                await axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then(async (data) => {
                    this.translate_global = data.data
                    if (this.client_id) {
                        await this.getParameters()
                    }
                })
            },
            async getParameters() {
                if (this.client_id) {
                    await axios.get(baseExternalURL + 'services/clients/parameter_search?client_id=' + this.client_id + '&lang=' + localStorage.getItem('lang')).then((result) => {
                        if (result.data.success) {
                            let data = result.data.data
                            let destinations = []
                            let interests = []
                            let days = []
                            let categories = [];
                            data.destinations.forEach(function(value) {
                                destinations.push({
                                    'code': value.code,
                                    'label': value.label,
                                    'country': value.country,
                                    'active': false,
                                })
                            })
                            this.destinations = destinations
                            data.interests.forEach(function(value) {
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
                                'label': this.translate_global.label.days,
                            })
                            data.days.forEach(function(value) {
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

                            this.getParamStorage()



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
                            } else {
                                // Default selection: PERU
                                let destinationsNames = [];
                                let destinationsCodes = [];
                                const defaultCountry = 'PERU';

                                for (let i = 0; i < this.destinations.length; i++) {
                                    // Case insensitive check for Peru
                                    if (this.destinations[i].country &&
                                        this.destinations[i].country.toUpperCase() === defaultCountry) {
                                        this.destinations[i].active = true;
                                        destinationsNames.push(this.destinations[i].label);
                                        destinationsCodes.push(this.destinations[i].code);
                                    }
                                }

                                if (destinationsCodes.length > 0) {
                                    this.selectedDestinations = destinationsCodes;
                                    this.selectedDestinationsLabel = destinationsNames.join(', ');
                                }
                            }
                            //Intereses seleccionados
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

                            //categorias seleccionados
                            console.log('Categorias seleccionados', this.selectedCategories.length)
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
                            console.log('Categorias seleccionados: selectedCategoriesLabel => ', this.selectedCategoriesLabel)

                        }
                    }, ).catch((e) => {
                        console.log(e)
                    })
                }
            },
            selectedDestiny(destiny) {
                destiny.active = !(destiny.active)
                this.updateSelectedDestinations()
            },
            toggleCountry(country) {
                const countryDestinations = this.groupedDestinations[country];
                const allSelected = countryDestinations.every(dest => dest.active);

                // Toggle all destinations in this country
                countryDestinations.forEach(dest => {
                    dest.active = !allSelected;
                });

                // Update selected destinations
                this.updateSelectedDestinations();
            },
            isCountryFullySelected(country) {
                const countryDestinations = this.groupedDestinations[country];
                return countryDestinations.length > 0 && countryDestinations.every(dest => dest.active);
            },
            isCountryPartiallySelected(country) {
                const countryDestinations = this.groupedDestinations[country];
                const selectedCount = countryDestinations.filter(dest => dest.active).length;
                return selectedCount > 0 && selectedCount < countryDestinations.length;
            },
            getCountrySelectedCount(country) {
                const countryDestinations = this.groupedDestinations[country];
                return countryDestinations.filter(dest => dest.active).length;
            },
            updateSelectedDestinations() {
                let destinationsCodes = [];
                let destinationsNames = [];

                for (let i = 0; i < this.destinations.length; i++) {
                    if (this.destinations[i].active) {
                        destinationsCodes.push(this.destinations[i].code);
                        destinationsNames.push(this.destinations[i].label);
                    }
                }

                this.selectedDestinationsLabel = destinationsNames.join(', ');
                this.selectedDestinations = destinationsCodes;
            },
            selectedInterest(interest) {
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
                console.log(category)
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
            async search() {
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
                    await axios.post(
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
            goToContactUs() {
                window.location.href = '/contact-us'
            },
            cleanFilters() {
                this.selectedDay = null
                this.selectedDestinations = []
                this.selectedInterests = []
                this.selectedCategories = []
                this.selectedDestinationsLabel = ''
                this.selectedInterestsLabel = ''
                this.selectedCategoriesLabel = ''
                this.filter = ''
                for (let i = 0; i < this.interests.length; i++) {
                    this.interests[i].active = false
                }
                for (let i = 0; i < this.categories.length; i++) {
                    this.categories[i].active = false
                }
                for (let i = 0; i < this.destinations.length; i++) {
                    this.destinations[i].active = false
                }

            },
            goToPackageDetails(pack) {
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
                    type_service: pack.type_services[0].id,
                    date_to_days: pack.nights + 1,
                    package_ids: [pack.id]
                }
                localStorage.setItem('parameters_packages_details', JSON.stringify(data))
                window.location = baseURL + 'package-details'
            },
            searchByType(type) {
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
<style>
    /* Grouped Destinations Dropdown Styles */
    .dropdown-menu--grouped {
        max-height: 400px;
        overflow-y: auto;
        padding: 0;
        min-width: 300px;
    }

    .destination-group {
        border-bottom: 1px solid #e9ecef;
    }

    .destination-group:last-child {
        border-bottom: none;
    }

    .destination-group__header {
        padding: 12px 16px;
        background-color: #f8f9fa;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #495057;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .destination-group__header:hover {
        background-color: #e9ecef;
        border-left-color: #eb5757;
    }

    .destination-group__checkbox {
        font-size: 16px;
        color: #eb5757;
        min-width: 16px;
    }

    .destination-group__title {
        flex: 1;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .destination-group__count {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        background-color: #fff;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .destination-group__items {
        background-color: #ffffff;
    }

    .checkbox-ui--nested {
        padding-left: 15px !important;
        font-size: 13px;
    }

    .checkbox-ui--nested i {
        font-size: 14px;
    }

    .dropdown-menu__option {
        padding: 8px 16px;
        transition: background-color 0.15s ease;
    }

    .dropdown-menu__option:hover {
        background-color: #f8f9fa;
    }

    .checkbox-ui {
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        margin: 0;
        user-select: none;
    }

    .checkbox-ui i {
        color: #eb5757;
        transition: transform 0.15s ease;
    }

    .checkbox-ui:hover i {
        transform: scale(1.1);
    }

    /* Scrollbar styling for dropdown */
    .dropdown-menu--grouped::-webkit-scrollbar {
        width: 6px;
    }

    .dropdown-menu--grouped::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .dropdown-menu--grouped::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .dropdown-menu--grouped::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Destination Tags Styles */
    .tag-destination {
        background-color: #f1f1f1;
        border-radius: 4px;
        padding: 2px 8px;
        /* Reduced padding */
        margin-right: 4px;
        font-size: 11px;
        color: #333;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        border: 1px solid #e0e0e0;
        white-space: nowrap;
    }

    .tag-destination__count {
        background-color: #eb5757;
        color: white;
        border-radius: 10px;
        padding: 0 5px;
        min-width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        line-height: 1;
    }

    /* Standard height form control */
    .form-control.destination {
        height: calc(1.5em + .75rem + 2px) !important;
        min-height: unset !important;
        padding: 0 0.75rem;
        /* Remove vertical padding */
        display: flex;
        align-items: center;
    }

    .form-control.destination .nav-link {
        padding: 0 !important;
        display: flex;
        align-items: center;
        height: 100%;
        width: 100%;
        color: #495057;
    }
</style>
@endsection
