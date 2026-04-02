<template>
    <div>
        <loading-component v-show="block_page"></loading-component>
        <template v-show="!block_page">
            <section class="clients-multimedia">
                <div class="hero__primary hero__multimedia">
                    <p class="pb-4">{{ translations.label.find }}</p>
                    <div class="searcher d-flex">
                        <div class="form-control dropdown destination" style="border-radius: 5px 0 0 5px;">
                            <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuDestinies"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="d-flex justify-content-between">
                                    <span class="text-left" >{{ translations.label.all_destinations }}</span>
                                    <i class="fas fa-sort-down"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuDestinies">
                                <div class="d-flex align-items-center dropdown-menu__option">
                                    <label class="checkbox-ui" v-on:click="toggleDestination()">
                                        <i :class="[(flag_all) ? 'fa fa-check-square' : 'far fa-square']"></i>
                                        {{ translations.label.all_destinations }}
                                    </label>
                                </div>
                                <div class="d-flex align-items-center dropdown-menu__option" v-for="(destiny, d) in destinations">
                                    <label class="checkbox-ui" v-on:click="toggleDestination(destiny)">
                                        <i :class="[(Object.values(destinations_selected).indexOf(destiny.path) > -1) ? 'fa fa-check-square' : 'far fa-square']"></i>
                                        {{ destiny.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="search col p-0">
                            <input type="text" class="search-term w-100" v-model="query" placeholder="¿Que buscas?">
                            <button type="button" class="search-button__delete" v-on:click="clearSearch()" v-if="query != ''">
                                <span class="icon-ac-x-circle"></span>
                            </button>
                            <button type="button" class="search-button" v-on:click="search(0)">
                                <span class="icon-search"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- clients-multimedia grid_multimedia-->
            <section class="container-fluid px-5" style="padding: 5rem 0;">
                <h2>{{ translations.label.filters }}</h2>
                <div class="row align-items-start">
                    <div class="col-2" style="position: sticky; top: 0; align-self: start;">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <div class="my-5" style="border: 1px dashed #ddd; border-radius: 10px;" v-for="(filter, f) in filters">
                                    <div class="panel-heading active" role="tab" v-bind:id="'heading_' + filter.folder">
                                        <h4 class="panel-title mb-0">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion" v-bind:href="'#collapse_' + filter.folder"
                                            aria-expanded="true" aria-controls="collapseOne">
                                                {{ showTranslation(filter.folder) }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div v-bind:id="'collapse_' + filter.folder" v-bind:class="['panel-collapse', 'collapse', ((f == 0) ? 'in' : '')]" role="tabpanel"
                                        v-bind:aria-labelledby="'heading_' + filter.folder">
                                        <div class="panel-body">
                                            <form class="m-4">
                                                <div class="justify-content-between align-items-center my-2">
                                                    <div class="d-flex align-items-center pb-4" v-for="(item, i) in filter.photo_filters">
                                                        <label class="checkbox-ui" v-on:click="changeFilter(item.tag)">
                                                            <i v-bind:class="['fa', (filters_selected[item.tag] == 1) ? 'fa-check-square' : 'fa-square']"
                                                                style="font-size:16px;"></i>
                                                            {{ (item.translations.length > 0) ? item.translations[0].value : item.code }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <button class="btn btn-primary w-100" type="button" v-on:click="search(0)">
                                                    <span class="icon-search"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div :class="['d-flex align-items-center py-5', 'justify-content-between']" style="position: sticky; top: 0; z-index: 10; background: #FFF;">
                            <button class="btn-primary" :disabled="Object.values(this.images_selected).length == 0"
                                type="button" v-on:click="downloadFiles()">
                                <span class="icon-ac-download mr-1"></span> {{ translations.label.download_selected }} ({{ Object.values(this.images_selected).length }})
                            </button>
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- span class="px-3">Mostrar</span -->

                                <ul v-if="pages > 1" class="pagination px-3" style="display: contents!important;">
                                    <li v-bind:class="(page > 0) ? '' : 'disabled'">
                                        <a v-bind:disabled="page > 0" href="javascript:;" v-on:click="search((page - 1))"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
                                    </li>
                                    <li v-bind:class="(page == _p) ? 'active' : ''" v-for="(_page, _p) in pages" v-if="show_pages[_p]">
                                        <a href="javascript:;" v-bind:class="[(page == _p) ? 'text-danger bg-light' : 'text-dark', 'px-3 py-2 mx-2']"
                                            style="border:1px solid #dee2e6;"
                                            v-on:click="search(_p)">
                                            <b v-if="(page == _p)">{{ _page }}</b>
                                            <span v-else>{{ _page }}</span>
                                        </a>
                                    </li>
                                    <li v-bind:class="(page < pages) ? '' : 'disabled'">
                                        <a v-bind:disabled="page < pages" href="javascript:;" v-on:click="search((page + 1))"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                                <div class="d-flex justify-content-between align-items-center px-3">
                                    <label for="to_page" class="m-4">{{ translations.label.goto_page }}</label>
                                    <input type="number" class="form-control" id="to_page" min="1" v-bind:max="pages" v-model="goto_page" style="width: 50px; ">
                                    <label for="to_page" class="m-4"> / <b>{{ pages }}</b></label>
                                </div>
                                <button class="btn-primary" v-on:click="gotoPage()">{{ translations.label.go }}</button>
                            </div>
                        </div>
                        <div style="border-radius: 10px;">
                            <div class="grid w-100" v-if="images.length > 0">
                                <template v-for="(image, i) in images">
                                    <div class="grid__item" v-bind:key="'image-' + i">
                                        <div class="card" style="height: auto; border: 1px dashed #ccc;border-radius: 10px;">
                                            <div class="position-absolute m-3" style="bottom:0; right:0;" v-on:click="changeImage(i)">
                                                <label class="checkbox-ui m-0">
                                                    <i v-bind:class="['fa', (Object.values(images_selected).indexOf(i) > -1) ? 'fa-check-square' : 'fa-square']"
                                                        style="font-size: 25px;"></i>
                                                </label>
                                            </div>
                                            <a v-bind:href="image.resizes.high" :data-caption="showCaption(image)" data-fancybox="gallery"
                                                :data-download-src="image.secure_url">
                                                <div class="embed-responsive embed-responsive-1by1 bg-light" v-if="image.resource_type == 'video'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" style="top: 50%;left: 50%;margin-left: -2em;margin-top: -2em;"
                                                        width="4em" height="4em" fill="currentColor" class="bi bi-file-play-fill position-absolute" viewBox="0 0 16 16">
                                                        <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM6 5.883a.5.5 0 0 1 .757-.429l3.528 2.117a.5.5 0 0 1 0 .858l-3.528 2.117a.5.5 0 0 1-.757-.43V5.884z"/>
                                                    </svg>
                                                </div>
                                                <img class="card__img" v-bind:src="linkSecure(image.resizes.low)" style="border-radius: 10px 10px 0 0;"
                                                    v-bind:alt="image.filename" v-if="image.resource_type == 'image'" />
                                            </a>
                                            <div class="p-3">
                                                <p>{{ showCaption(image) }}</p>
                                                <p class="mb-0">
                                                    <a href="javascript:;" v-on:click="downloadFile(image)">
                                                    <span class="icon-ac-download mr-1"></span> {{ translations.label.download }}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="alert alert-warning" v-else>{{ translations.label.no_data }}</div>
                        </div>
                        <!-- /div -->
                    </div>
                </div>

            </section>
        </template>
    </div>
</template>

<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                translations: {
                    label: {},
                },
                block_page: true,
                lang: '',
                dayNow: '',
                filters: [],
                pages: 0,
                images: [],
                total_images: 0,
                destinations: [],
                destinations_selected: {},
                filters_selected: {},
                pager: {},
                photos: [],
                slidePhotos: 0,
                goto_page: 1,
                limit: 0,
                images_selected: {},
                query: '',
                page: 0,
                slide: 0,
                flag_all: true,
                show_pages: [],
                view_pages: 8,
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.dayNow = new Date()

            this.setTranslations()
        },
        computed: {

        },
        methods: {
            showCaption(image) {
                if(image.context)
                {
                    return image.context[`title_${this.lang}`] ?? image.context?.caption ?? image.filename
                }
                else
                {
                    return image.context?.caption ?? image.filename
                }
            },
            linkSecure(_link) {
                return _link.replace(/^http:/, 'https:');
            },
            initialize: function () {
                this.searchDestinations()
                this.searchFilters()
            },
            setTranslations: function () {
                //axios.get('https://aurora.limatours.com.pe/'+'translation/'+this.lang+'/slug/multimedia').then((data) => {
                axios.get(baseURL+'translation/'+this.lang+'/slug/multimedia').then((data) => {
                    this.translations = data.data
                    this.initialize()
                })
            },
            showTranslation: function (_folder) {
                return eval("this.translations.label." + _folder);
                // return this.translations.label[_folder]
            },
            prev () {
                this.$refs.carouselPhotos.prev()
            },
            next () {
                this.$refs.carouselPhotos.next()
            },
            setSlide (index) {
                this.$refs.carouselPhotos.setSlide(index)
            },
            gotoPage: function () {
                this.search(this.goto_page)
            },
            searchDestinations: function () {
                axios.get('api/multimedia/destinations?lang=' + this.lang)
                    .then((result) => {
                        this.block_page = false
                        if (result.data.success) {
                            this.destinations = result.data.data
                            this.getDestinationsAll()
                        }
                    }).catch((e) => {
                    this.block_page = false
                    console.log(e)
                })
            },
            toggleDestination: function (destiny) {

                if(destiny == undefined)
                {
                    this.destinations_selected = []
                    this.flag_all = !this.flag_all

                    if(this.flag_all)
                    {
                        this.getDestinationsAll()
                    }
                }
                else
                {
                    if(this.destinations_selected.length == 0)
                    {
                        this.destinations_selected.push(destiny.path)
                    }
                    else
                    {
                        let delete_ = false
                        this.destinations_selected.forEach((item, i) => {
                            if(item == destiny.path)
                            {
                                this.destinations_selected.splice(i, 1)
                                delete_ = true
                                this.flag_all = false
                            }


                            if(delete_ == false && ((this.destinations_selected.length - 1) == i))
                            {
                                this.destinations_selected.push(destiny.path)
                                // this.destinations_search.push(destiny.)
                            }
                        })
                    }


                    if(this.destinations_selected.length == this.destinations.length)
                    {
                        this.flag_all = true
                    }
                }


                console.log(this.destinations_selected)
            },
            getDestinationsAll: function () {
                let checkDestinations = []
                for (let i = 0; i < this.destinations.length; i++) {
                    checkDestinations.push(this.destinations[i].path)
                }
                this.destinations_selected = checkDestinations
            },
            changeImage: function (_image) {

                if(this.images_selected[_image] == undefined)
                {
                    Vue.set(this.images_selected, _image, _image)
                }
                else
                {
                    Vue.delete(this.images_selected, _image)
                }
            },
            changeFilter: function (filter) {

                if(this.filters_selected[filter] != 1)
                {
                    Vue.set(this.filters_selected, filter, 1)
                }
                else
                {
                    Vue.set(this.filters_selected, filter, 0)
                }
            },
            validatePagination: function () {
                this.view_pages = 8
                let page = this.page
                let pages = this.pages

                for(let p=0;p<pages;p++)
                {
                    this.show_pages[p] = false

                    if(page < this.view_pages)
                    {
                        if(this.view_pages > 0)
                        {
                            this.view_pages -= 1
                            this.show_pages[p] = true
                        }
                    }
                    else
                    {
                        if(page >= (pages - (this.view_pages) / 2))
                        {
                            if(p >= (pages - this.view_pages))
                            {
                                this.show_pages[p] = true
                            }
                        }
                        else
                        {
                            if(p >= parseFloat(page - parseFloat(this.view_pages / 2)) && p <= parseFloat(page + parseFloat(this.view_pages / 2)))
                            {
                                this.show_pages[p] = true
                            }
                        }
                    }
                }

            },
            searchFilters: function () {
                let vm = this

                axios.get(baseExternalURL + 'api/multimedia/filters?lang=' + this.lang)
                    .then((result) => {
                        this.block_page = false
                        if (result.data.success) {
                            this.filters = result.data.data

                            this.filters.forEach((filter, f) => {

                                filter.photo_filters.forEach((item, i) => {
                                    Vue.set(this.filters_selected, item.tag, 0)

                                    /*
                                    if(i == 0 && f == 0)
                                    {
                                        Vue.set(this.filters_selected, item.tag, 1)
                                    }
                                    */
                                })
                            })

                            setTimeout(function() {
                                vm.search(0)
                            }, 1000)
                        }
                    }).catch((e) => {
                    this.block_page = false
                    console.log(e)
                })
            },
            clearSearch: function () {
                this.query = ''
                this.search(0)
            },
            search: function (_page) {

                if(this.page != _page || _page == 0)
                {
                    this.block_page = true

                    this.page = _page
                    this.images = []
                    this.images_selected = {}

                    let data = {
                        lang: this.lang,
                        page: _page,
                        // next_page: '',
                        destinations: this.destinations_selected,
                        filters: this.filters_selected,
                        filter: this.query,
                    }

                    axios.post('api/destinations/search', data)
                        .then((result) => {

                            if (result.data.success === true && result.data.data.length > 0) {
                                this.images = result.data.data
                                this.pages = result.data.pages
                                this.total_images = result.data.total
                                this.limit = result.data.limit
                                // this.total_images_response += result.data.data.length
                            } else {
                                this.images = []
                                this.pages = 0
                                this.total_images = 0
                            }

                            this.validatePagination()
                            this.block_page = false
                        })
                        .catch((e) => {
                            this.block_page = false
                            console.log(e)
                        })
                }
            },
            downloadFiles: function () {
                Object.values(this.images_selected).forEach((item, i) => {
                    this.downloadFile(this.images[item])
                });
            },
            downloadFile: function (_image) {
                axios.get(
                    _image.resizes.high.replace('http:', 'https:'),
                    { responseType: 'blob' }
                ).then((response) => {
                    if (localStorage.getItem("user_type_id") == 4) {
                        dataLayer.push({
                            event: "download",
                            file_category: "multimedia"
                        });
                    }
                    var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                    var fileLink = document.createElement('a')
                    fileLink.href = fileURL
                    fileLink.setAttribute('download', _image.filename + '.' + _image.format)
                    document.body.appendChild(fileLink)
                    fileLink.click()
                    image.download = false
                }).catch((e) => {
                    // image.download = falses
                    console.log(e)
                })
            }
        }
    };
</script>
