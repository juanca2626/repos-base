<template>

    <div class="modal modal--cotizacion" id="modalCotizaciones" tabindex="-1" role="dialog" style="overflow: scroll;">
        <loading-component v-show="loadingPrincipal" style="z-index: 99999;"></loading-component>
        <div class="modal-dialog modal--cotizacion__document" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>{{ translations.label.my_quotes }}</b></h3>
                        <div class="row">
                            <div class="col-12 m-4">
                                <div class="row">
                                    <div class="col-sm-auto"><span class="modal-paragraph">{{
                                            translations.label.total
                                        }}: <b>{{ totals.total }}</b></span>
                                    </div>
                                    <div class="col-sm-auto"> <span class="modal-paragraph">{{
                                            translations.label.new
                                        }}: <b>{{ totals.news }} </b><i
                                            class="alert alert-info"></i></span></div>
                                    <div class="col-sm-auto"> <span class="modal-paragraph">{{
                                            translations.label.active
                                        }}: <b>{{ totals.activated }} </b><i
                                            class="alert alert-dark"></i></span></div>
                                    <div class="col-sm-auto">
                                        <span class="modal-paragraph">{{ translations.label.expired }}:
                                            <b>{{ totals.expired }}</b><i class="alert alert-danger"></i>
                                        </span>
                                    </div>
                                    <div class="col-sm-auto">
                                        <span class="modal-paragraph">
                                            <a href="javascript:;" data-toggle="modal" data-target="#modalImports">
                                                <b>{{ translations.label.import_more }} ...</b><i
                                                class="icon-download"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <form class="form">
                            <div class="form-row d-flex align-items-center mb-0">
                                <div class="col-auto mb-4" v-if="user_type_id != 4">
                                    <label><input type="radio" value="E" name="filter_user_type"
                                                v-model="filterUserType"/> {{ translations.label.specialist }}</label>
                                    <label><input type="radio" value="C" name="filter_user_type"
                                                v-model="filterUserType"/> {{ translations.label.client }}</label>
                                </div>
                                <div class="col mb-4">
                                    <v-select class="form-control" v-model="filterBy" :options="all_status"
                                            :reduce="status => status.code" code="code" label="label"
                                            @input="changeFilterBy">
                                    </v-select>
                                </div>
                                <div class="col mb-4" v-if="more_client_sellers.length>1">
                                    <v-select class="form-control with-icon" v-model="executive"
                                        :options="more_client_sellers" :reduce="client_seller => client_seller.id"
                                        code="id" label="label" @input="searchQuotes">
                                    </v-select>
                                </div>
                                <div class="col mb-4" v-if="markets.length>0">
                                    <v-select class="form-control with-icon" v-model="market" :options="markets"
                                            :reduce="market => market.id" code="id" label="name"
                                            v-bind:placeholder="translations.label.my_quotes"
                                            @input="changeMarket">
                                    </v-select>
                                </div>
                                <div class="col mb-4" v-if="filterUserType == 'C' && clients_market.length>0">
                                    <v-select :options="clients_market" v-model="client"
                                        code="code" label="label" @input="searchQuotes" autocomplete="off"
                                        v-bind:placeholder="translations.validations.rq_client"
                                        class="form-control with-icon"></v-select>
                                </div>
                                <div class="col mb-4" v-if="filterUserType == 'E' && executives.length > 0">
                                    <v-select :options="executives" v-model="executive"
                                        @input="searchQuotes" label="name" code="id" :reduce="executive => executive.id"
                                        v-bind:placeholder="translations.label.show_all"
                                        class="form-control with-icon"></v-select>
                                    <!-- select class="form-control" style="padding: 9px 12px;" v-model="executive"
                                            v-on:change="searchQuotes()">
                                        <option selected value="">{{ translations.label.show_all }}</option>
                                        <option v-bind:value="_executive.id" v-for="(_executive, e) in executives">
                                            {{ _executive.name }}
                                        </option>
                                    </select -->
                                </div>
                                <div class="col-auto mb-4">
                                    <div class="input-group"> <span
                                        class="input-group-text"><i
                                        class="icon-search"></i></span>
                                        <input :disabled="loading" class="form-control" id="query_quotes"
                                            v-model="query_quotes" type="text"
                                            :placeholder="translations.label.search_quote + ' ...'">
                                    </div>
                                </div>
                                <div class="col-auto mb-4">
                                    <div
                                        class="dropdown text-center">
                                        <a href="javascript:;" role="button" id="dropdownMenuLink3"
                                        data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                            <i class="btn-icon fas fa-map-marker-alt"></i>
                                            <span class="subtitulo">{{ translations.label.destiny }}</span>
                                        </a>
                                        <div class="dropdown-menu p-4" aria-labelledby="dropdownMenuLink3">
                                            <div class="form-group scrollbar-outer">
                                                <label class="form-check form-check-all">
                                                    <input class="form-check-input" type="checkbox"
                                                        @change="toggleAllDestinations()"
                                                        v-model="checkedAllDestinations">
                                                    <span>{{ translations.label.all_destinations }}</span>
                                                </label>
                                                <label class="form-check" v-for="destiny in filter_by_destiny">
                                                    <input class="form-check-input" type="checkbox"
                                                        v-model="destiny.checked">
                                                    <span>{{ destiny.name }}</span>
                                                </label>
                                            </div>
                                            <button class="btn btn-danger" type="button" @click="changeFilterBy()"
                                                    style="width: 120px">
                                                <i class="fa fa-search mr-1"></i> {{ translations.label.search }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-auto mb-4">
                                    <a href="/packages/cotizacion" class="btn btn-primary"
                                       style="width:100% !important;">{{ translations.label.see_board }}</a>
                                </div>
                            </div>
                        </form>
                        <div class="alert alert-warning" v-if="loading">
                            {{ translations.label.loading }}
                        </div>

                        <div class="tbl--cotizacion" v-if="!loading">

                            <div v-if="quotes.length>0" class="tbl--cotizacion__header">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-sm-auto px-3">
                                        <div class="tbl--cotizacion__delete"></div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__codigo">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.rate_code }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__detalles">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.detail }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__fecha">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.date_from }} / {{
                                                    translations.label.status
                                                }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__ciudad">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.cities }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__duracion">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.duration }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto px-3">
                                        <div class="tbl--cotizacion__tipo">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.type }}</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__acciones">
                                            <h4 class="tbl--cotizacion__title">{{ translations.label.actions }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="quotes.length>0"
                                 :class="'tbl--cotizacion__content tbl--cotizacion__' + q.backRow" v-for="q in quotes">
                                <div class="row no-gutters align-items-center">
                                    <div class="col-sm-auto px-3">
                                        <a href="javascript:;" data-toggle="modal"
                                           data-target="#modalWillRemove" @click="willRemove(q)"
                                           class="tbl--cotizacion__item tbl--cotizacion__delete text-center">
                                            <span class="icon-trash-2"></span>
                                        </a>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__codigo">
                                            <span>#{{ q.id }}</span>
                                            <a href="javascript:;" class="acciones__item">
                                                <span class="icon--acciones">
                                                    <i data-toggle="modal"
                                                       data-target="#modalAlerta" id="hidden-modal-alert"></i>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                            <span><strong>{{ q.name }}</strong></span>
                                            <span>{{ translations.label.created }}: {{ q.created_at | formatDate }}<br/>
                                                <span v-if="q.reservation" class="text-danger font-weight-bold file-number file-number-a2">
                                                  File #: {{ q.reservation.file_code }}
                                                </span>
                                                <span v-else-if="q.file_id" class="text-danger font-weight-bold file-number file-number-a3">
                                                  File #: {{ q.file_number }}
                                                </span>
                                                <strong>{{ q.user.code }}</strong><br/>
                                                <a href="javacript:;" style="float: right;" class="cursor-pointer"
                                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                   v-if="q.sent_logs_count>0">
                                                    <i class="fa fa-share" :title="translations.label.see_users"></i> {{
                                                        q.sent_logs_count
                                                    }}
                                                </a>
                                                <div class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-left"
                                                     style="overflow-y: scroll;">
                                                    <div class="dropdown-menu_body">
                                                        <ul class="list-group scrollbar-outer mt-4"
                                                            style="margin-bottom: 45px;">
                                                            <li class="list-group-item" v-for="log_u in q.log_user"
                                                                v-if="log_u.type=='copy_to'">
                                                                <div class="row">
                                                                    <div class="col-3">
                                                                        <p>
                                                                            <i class="fa fa-share"></i> {{
                                                                                log_u.created_at | formatDate
                                                                            }}
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-9">
                                                                        <p>
                                                                             <span v-if="log_u.user">({{
                                                                                     log_u.user.code
                                                                                 }}) {{ log_u.user.name }}</span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </span>
                                            <span>
                                                {{ q.detail }}
                                            </span>
                                            <span v-if="q.shared == 1" style="color: darkred">
                                                {{ translations.label.shared_with }}: ({{ q.permission.client.code }})
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__fecha">
                                            <span><b>{{ q.date_in | reformatDate }}</b></span>
                                            <span>({{ q.when_it_starts }})</span>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__ciudad">
                                            <span class="tag" v-for="destiny in q.destinations">
                                                {{ destiny?.state?.iso || 'NO ISO' }}
                                            </span>
                                            <span v-if="q.destinations.length==0">-</span>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__duracion text-center">
                                            <span>{{ q.nights }}</span><small>{{ translations.label.nights }}</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto px-3">
                                        <div class="tbl--cotizacion__item tbl--cotizacion__tipo text-center">
                                            <span>{{ q.service_type.code }}</span>
                                        </div>
                                    </div>
                                    <div class="col px-3">

                                        <div
                                            class="tbl--cotizacion__item tbl--cotizacion__acciones tbl--cotizacion__estimado text-center"
                                            v-if="q.code!='' && q.code!=null && q.categories.length == 0">
                                            <div class="dropdown-group">
                                                <a href="javascript:;" @click="importServices(q)" class="acciones__item"
                                                   v-if="!q.loadingRow">
                                                    <span class="icon--acciones">
                                                        <i class="icon-download"></i> <span>{{
                                                            translations.label.import_informix_services
                                                        }}</span>
                                                    </span>
                                                </a>
                                                <a href="javascript:;" @click="importServices(q)" class="acciones__item"
                                                   v-if="q.loadingRow">
                                                    <span class="icon--acciones">
                                                        <i class="fa fa-spin fa-spinner"></i> <span>{{
                                                            translations.label.importing
                                                        }} ... </span>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="tbl--cotizacion__item tbl--cotizacion__acciones text-center"
                                             v-if="q.categories.length > 0">
                                            <div class="dropdown-group">
                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                   class="acciones__item" @click="viewServices(q)">
                                                        <span class="icon--acciones">
                                                            <i class="icon-eye" :title="translations.label.detail"></i>
                                                        </span>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu__cotizacion dropdown-menu-right"
                                                     style="overflow-y: scroll;">
                                                    <div class="dropdown-menu_body" v-if="categoriesForView.length>0">

                                                        <h4 class="dropdown-menu_title"><b>{{
                                                                translations.label.quote
                                                            }} {{ q.id }}</b></h4>

                                                        <small class="dropdown-menu_subtitle">{{
                                                                translations.label.this_category_has
                                                            }}
                                                            <b>{{ servicesForView.length }} {{
                                                                    translations.label.products
                                                                }}</b>:</small>

                                                        <div class="cotizacion-categorias"
                                                             style="margin-bottom: 5px; padding: 0;">
                                                            <button :class="'btn btn-tab categoria ' + qCateg.tabActive"
                                                                    type="button"
                                                                    @click.stop="toggleTabCategory(qCateg)"
                                                                    v-for="qCateg in categoriesForView">
                                                                <span v-if="qCateg.type_class.translations.length > 0">{{
                                                                        qCateg.type_class.translations[0].value
                                                                    }}</span>
                                                            </button>
                                                        </div>

                                                        <ul class="list-group scrollbar-outer mt-4"
                                                            style="margin-bottom: 45px;">
                                                            <li class="list-group-item"
                                                                v-for="(serv,keyS) in servicesForView">
                                                                <div class="row no-gutters">
                                                                    <div class="col-1">
                                                                        <p><small>{{ keyS + 1 }}.</small></p>
                                                                    </div>
                                                                    <div class="col-2">
                                                                        <p>
                                                                            <b v-if="serv.type == 'service'">
                                                                                {{ serv.service.aurora_code }}
                                                                            </b>
                                                                            <b v-if="serv.type == 'hotel'">
                                                                                {{ serv.hotel.channel[0].code }}
                                                                            </b>
                                                                            <b v-if="serv.type == 'flight'">
                                                                                {{ translations.label.flight }}
                                                                            </b>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-8">
                                                                        <p class="ml-3">
                                                                            <b v-if="serv.type == 'service'">
                                                                                {{ serv.service.name }}
                                                                            </b>
                                                                            <b v-if="serv.type == 'hotel'">
                                                                                {{ serv.hotel.name }}
                                                                            </b>
                                                                            <b v-if="serv.type == 'flight'">
                                                                                <span
                                                                                    v-if="serv.code_flight == 'AEC' || serv.code_flight == 'AECFLT'">
                                                                                    NACIONAL
                                                                                </span>
                                                                                <span
                                                                                    v-if="serv.code_flight == 'AEI' || serv.code_flight == 'AEIFLT'">
                                                                                    INTERNACIONAL
                                                                                </span>
                                                                            </b>
                                                                        </p>
                                                                        <p class="ml-3">
                                                                            <small>{{ serv.date_in }} -
                                                                                <span v-if="serv.type=='service'">
                                                                                    {{ serv.adult }} ADL / {{
                                                                                        serv.child
                                                                                    }} CHD / {{ serv.infant }} INF
                                                                                </span>
                                                                                <span v-if="serv.type=='hotel'">
                                                                                    {{
                                                                                        serv.single
                                                                                    }} SGL / {{
                                                                                        serv.double
                                                                                    }} DBL / {{ serv.triple }} TPL <br> {{
                                                                                        serv.nights
                                                                                    }}
                                                                                    {{ translations.label.nights }}
                                                                                </span>
                                                                                <span v-if="serv.type=='flight'">
                                                                                    {{ serv.origin }} <i
                                                                                    class="fas fa-long-arrow-alt-right"></i> {{
                                                                                        serv.destiny
                                                                                    }}
                                                                                </span>
                                                                            </small>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-1">
                                                                        <i class="icon-circle text-success"
                                                                           v-if="serv.on_request === 0"></i>
                                                                        <i class="icon-circle text-danger"
                                                                           v-if="serv.on_request === 1"></i>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="dropdown-menu_body" v-if="categoriesForView.length==0">
                                                        <center><i class="fa fa-spin fa-spinner fa-2x"></i></center>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="dropdown-group" v-if="( class_market != '' || market == '' )">

                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                   class="acciones__item" :title="translations.label.edit">
                                                    <span class="icon--acciones"><i class="icon-edit"></i></span>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu__opciones dropdown-menu-right">
                                                    <div class="dropdown-menu_body"
                                                         v-if="q.categories.length==1 &&
                                                           ( q.categories[0].type_class.code=='x' || q.categories[0].type_class.code=='X' )">
                                                        {{ translations.label.please_choose_categories }}.
                                                        <label v-for="c in type_classes"
                                                               @click.stop="changeTypeClass(c)">
                                                            <input :disabled="loading" type="checkbox"
                                                                   :checked="c.check" v-model="c.check"> ({{ c.code }})
                                                            <span v-if="c.translations.length > 0">{{
                                                                    c.translations[0].value
                                                                }}</span>
                                                        </label>
                                                        <br>
                                                        <button :disabled="loading" class="btn btn-sm btn-success"
                                                                type="button"
                                                                @click="replaceMultiple(q.id, q.categories[0].id)">
                                                            <i class="fa fa-save" v-if="!loading"></i>
                                                            <i class="fa fa-spin fa-spinner" v-if="loading"></i> {{
                                                                translations.label.save
                                                            }}
                                                        </button>
                                                    </div>
                                                    <div class="dropdown-menu_body"
                                                         v-if="q.categories.length>1 ||
                                                           ( q.categories.length==1 &&
                                                            ( q.categories[0].type_class.code!='x' && q.categories[0].type_class.code!='X' ) )">
                                                        <div>
                                                            <div v-if="(!q.showForAdd && user_type_id == 3) ||
                                                            (q.permission !=null && user_type_id==4 && q.permission.edit_permission == 1) || (q.user_id == user_id) || verify_seller(q.user_id)">
                                                                <a href="javascript:;" @click="willEdit(q)"
                                                                   v-if="!q.editing_quote_user.editing">
                                                                    {{ translations.label.edit }} <i class="icon-edit"></i>
                                                                </a>
                                                            </div>
                                                            <div>
                                                                <a href="javascript:;" @click="duplicate(q)">
                                                                    {{ translations.label.duplicate }} <i
                                                                    class="icon-copy"></i>
                                                                </a>
                                                            </div>
                                                            <div v-if="(!q.showForAdd && user_type_id == 3) ||
                                                            (q.permission !=null && user_type_id==4 && q.permission.edit_permission == 1)|| (q.user_id == user_id) || verify_seller(q.user_id)">
                                                                <a href="javascript:;"
                                                                   @click.stop="willAdd(q)">{{ translations.label.add }}
                                                                    <i class="icon-file-text"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div v-if="q.showForAdd">
                                                            <form>
                                                                <h5>{{ translations.label.category }}:</h5>
                                                                <div class="ml-2">
                                                                    <label v-for="c in q.categories"
                                                                           @click.stop="q.radioCategories=c.id">
                                                                        <input :disabled="loading" name="category_radio"
                                                                               type="radio" :value="c.id"
                                                                               v-model="q.radioCategories">
                                                                        ({{ c.type_class.code }}) <span
                                                                        v-if="c.type_class.translations.length > 0">{{
                                                                            c.type_class.translations[0].value
                                                                        }}</span>
                                                                    </label>
                                                                </div>
                                                                <div class="d-flex my-3">
                                                                    <button :disabled="loading"
                                                                            class="btn btn-secondary mr-1" type="button"
                                                                            @click.stop="q.showForAdd=false">
                                                                        {{ translations.label.cancel }}
                                                                    </button>
                                                                    <button :disabled="loading" class="btn btn-primary"
                                                                            type="button" @click="add(q)">
                                                                        <i class="fa fa-spin fa-spinner"
                                                                           v-if="loading"></i> {{
                                                                            translations.label.add
                                                                        }}
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="dropdown-group" v-if="q.categories.length>1 ||
                                                           ( q.categories.length==1 &&
                                                            ( q.categories[0].type_class.code!='x' && q.categories[0].type_class.code!='X' ) )" >
                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                   class="acciones__item"
                                                    :title="q.order_related || filterUserType == 'C' ? translations.label.download : translations.label.order_related_required"
                                                     @click="downloadDropdown(q)">
                                                    <span class="icon--acciones"><i class="icon-download"></i></span>

                                                </a>
                                                <div class="dropdown-menu dropdown-menu__opciones dropdown-menu-right" style="padding: 22px 13px;" v-show="q.order_related || filterUserType == 'C'">
                                                    <div class="dropdown-menu_body">
                                                        <div v-if="!q.showDownloadSkeleton && !q.showDownloadItinerary" @click="clickDropDown($event)" class="miniMenu" style="display: block">
                                                            <a href="javascript:;"
                                                               @click.stop="willDownloadSkeleton(q)">
                                                                Skeleton <i class="icon-download"></i>
                                                            </a>
                                                            <a :href="getRouteExport(q)"
                                                               v-if="q.operation != '' && q.operation != null">
                                                                Excel <i class="icon-download"></i>
                                                            </a>
                                                            <a href="javascript:;"
                                                               @click.stop="willDownloadItinerary(q)">
                                                                {{ translations.label.itinerary }} <i
                                                                class="icon-download"></i>
                                                            </a>
                                                        </div>
                                                        <div v-if="q.showDownloadSkeleton" class="showDownloadSkeleton" @click="clickDropDown($event)">
                                                            <form>
                                                                <h5>Skeleton <i class="icon-download"></i></h5>
                                                                <hr>
                                                                <div class="ml-2">
                                                                    <input type="text" click.stop="" v-model="refPax"
                                                                           :placeholder="translations.label.reference_pax"
                                                                           style="border: solid 1px #d1d1d1; padding: 0 5px;">
                                                                </div>
                                                                <h5>{{ translations.label.category }}:</h5>
                                                                <div class="ml-2">
                                                                    <label class="d-flex align-items-center"
                                                                           v-for="c in q.categories"
                                                                           @click.stop="q.radioCategories=c.id">
                                                                        <input :disabled="loading" name="category_radio"
                                                                               type="radio" :value="c.id"
                                                                               v-model="q.radioCategories">
                                                                        ({{ c.type_class.code }}) <span
                                                                        v-if="c.type_class.translations.lenght > 0">{{
                                                                            c.type_class.translations[0].value
                                                                        }}</span>
                                                                    </label>
                                                                </div>
                                                                <h5>{{ translations.label.language }}:</h5>
                                                                <div class="ml-2">
                                                                    <select @click.stop=""
                                                                            v-model="language_for_download">
                                                                        <option value="es" selected>{{
                                                                                translations.label.spanish
                                                                            }}
                                                                        </option>
                                                                        <option value="en">{{
                                                                                translations.label.english
                                                                            }}
                                                                        </option>
                                                                        <option value="pt">{{
                                                                                translations.label.portuguese
                                                                            }}
                                                                        </option>
                                                                        <!-- <option value="it">{{
                                                                                translations.label.italian
                                                                            }}
                                                                        </option> -->
                                                                    </select>
                                                                </div>
                                                                <h5>{{ translations.label.header }}:</h5>
                                                                <div class="d-flex align-items-center">
                                                                    <label class=" mx-3" @click.stop="">
                                                                        <input :disabled="loading"
                                                                               name="with_header_radio" type="radio"
                                                                               :value="true" v-model="q.withHeader"> Sí
                                                                    </label>
                                                                    <label class=" mx-3" @click.stop="">
                                                                        <input :disabled="loading"
                                                                               name="with_header_radio" type="radio"
                                                                               :value="false" v-model="q.withHeader"> No
                                                                    </label>
                                                                    <br>
                                                                </div>
                                                                <div class="d-flex my-3">
                                                                    <button :disabled="loading"
                                                                            class="btn btn-secondary mx-1" type="button"
                                                                            @click.stop="q.showDownloadSkeleton=false">
                                                                        {{ translations.label.cancel }}
                                                                    </button>
                                                                    <button :disabled="loading"
                                                                            class="btn btn-primary mx-1" type="button"
                                                                            @click.stop="downloadSkeleton(q)">
                                                                        <i class="fa fa-spin fa-spinner"
                                                                           v-if="loading"></i> {{
                                                                            translations.label.download
                                                                        }}
                                                                    </button>
                                                                </div>
                                                            </form>

                                                        </div>
                                                        <div v-if="q.showDownloadItinerary" class="showDownloadItinerary" @click="clickDropDown($event)">
                                                            <form>
                                                                <h5>{{ translations.label.itinerary }} <i
                                                                    class="icon-download"></i></h5>
                                                                <hr>
                                                                <div class="ml-2">
                                                                    <input type="text" click.stop="" v-model="refPax"
                                                                           :placeholder="translations.label.reference_pax"
                                                                           style="border: solid 1px #d1d1d1; padding: 0 5px;">
                                                                </div>
                                                                <h5>{{ translations.label.category }}:</h5>
                                                                <div class="ml-2">
                                                                    <label class="d-flex align-items-center"
                                                                           v-for="c in q.categories"
                                                                           @click.stop="q.radioCategories=c.id">
                                                                        <input :disabled="loading" name="category_radio"
                                                                               type="radio" :value="c.id"
                                                                               v-model="q.radioCategories">
                                                                        ({{ c.type_class.code }}) <span
                                                                        v-if="c.type_class.translations.length > 0">{{
                                                                            c.type_class.translations[0].value
                                                                        }}</span>
                                                                    </label>
                                                                </div>
                                                                <h5>{{ translations.label.language }}:</h5>
                                                                <div class="ml-2">
                                                                    <select @click.stop=""
                                                                            v-model="language_for_download">
                                                                        <option value="es" selected>{{
                                                                                translations.label.spanish
                                                                            }}
                                                                        </option>
                                                                        <option value="en">{{
                                                                                translations.label.english
                                                                            }}
                                                                        </option>
                                                                        <option value="pt">{{
                                                                                translations.label.portuguese
                                                                            }}
                                                                        </option>
                                                                        <!-- <option value="it">{{
                                                                                translations.label.italian
                                                                            }}
                                                                        </option> -->
                                                                    </select>
                                                                </div>
                                                                <h5>{{ translations.label.do_you_want_a_cover }}:</h5>
                                                                <div class="d-flex align-items-center">
                                                                    <label class=" mx-3" @click.stop="">
                                                                        <input :disabled="loadingPrincipal"
                                                                               name="with_header_radio_header" type="radio"
                                                                               :value="true" v-model="q.withHeader"
                                                                               @change="setWithClientLogo(q)"> Sí
                                                                    </label>
                                                                    <label class=" mx-3" @click.stop="">
                                                                        <input :disabled="loadingPrincipal"
                                                                               name="with_header_radio_header" type="radio"
                                                                               :value="false" v-model="q.withHeader"
                                                                               @change="setWithClientLogo(q)"> No
                                                                    </label>
                                                                </div>
                                                                <div class="d-flex align-items-center" @click.stop="">
                                                                    <!-- <select @click.stop=""
                                                                            v-model="select_itinerary_with_cover"
                                                                            v-if="q.withHeader">
                                                                        <option value="0000">PORTADA</option>
                                                                        <option value="0001">FAMILIA1</option>
                                                                        <option value="0002">AVENTURA</option>
                                                                        <option value="0003">LUJO</option>
                                                                        <option value="0004">MACHU PICCHU</option>
                                                                        <option value="0005">CUSCO</option>
                                                                        <option value="0006">COLCA</option>
                                                                        <option value="0007">NASCA</option>
                                                                        <option value="0008">VALLE</option>
                                                                        <option value="0009">TRUJILLO</option>
                                                                        <option value="0010">PUNO</option>
                                                                        <option value="0011">LIMA</option>
                                                                        <option value="0019">VINICUNCA</option>
                                                                        <option value="0020">AREQUIPA</option>
                                                                        <option value="0012">PUERTO MALDONADO</option>
                                                                        <option value="0013">NORTE</option>
                                                                        <option value="0014">KUELAP</option>
                                                                        <option value="0015">FAMILIA2</option>
                                                                        <option value="0016">CAMINO_INCA</option>
                                                                        <option value="0017">BALLESTAS</option>
                                                                        <option value="0018">AMAZONAS</option>
                                                                        <option value="0021">MARAS</option>
                                                                        <option value="0022">MORAY</option>
                                                                        <option value="0023">AREQUIPA CATEDRAL</option>
                                                                        <option value="0024">COMUNIDAD LOCAL</option>
                                                                        <option value="0025">FAMILIA</option>
                                                                        <option value="0026">MPI2</option>
                                                                        <option value="0027">CUSCO IGLESIA</option>
                                                                        <option value="0028">MAPI</option>
                                                                        <option value="0029">FAMILIA 3</option>
                                                                        <option value="0030">LIMA1</option>
                                                                        <option value="0031">LIMA2</option>
                                                                    </select> -->
                                                                    <select @click.stop="" v-model="select_itinerary_with_cover"
                                                                        @change="setComboPortada(q)"
                                                                        class="showWithCover"
                                                                        v-if="urlPortada && q.withHeader !=''">
                                                                        <option value="amazonas">AMAZONAS</option>
                                                                        <option value="arequipa">AREQUIPA</option>
                                                                        <option value="arequipa-catedral">AREQUIPA CATEGRAL</option>
                                                                        <option value="argentina">ARGENTINA</option>
                                                                        <option value="aventura">AVENTURA</option>
                                                                        <option value="ballestas">BALLESTAS</option>
                                                                        <option value="bolivia">BOLIVIA</option>
                                                                        <option value="brasil">BRASIL</option>
                                                                        <option value="camino-inca">CAMINO INCA</option>
                                                                        <option value="chile">CHILE</option>
                                                                        <option value="colca">COLCA</option>
                                                                        <option value="comunidad-local">COMUNIDAD LOCAL</option>
                                                                        <option value="cusco">CUSCO</option>
                                                                        <option value="cusco-iglesia">CUSCO IGLESIA</option>
                                                                        <option value="familia1">FAMILIA1</option>
                                                                        <option value="familia2">FAMILIA2</option>
                                                                        <option value="familia3">FAMILIA3</option>
                                                                        <option value="familia4">FAMILIA4</option>
                                                                        <option value="huaraz">HUARAZ</option>
                                                                        <option value="kuelap">KUELAP</option>
                                                                        <option value="lima1">LIMA1</option>
                                                                        <option value="lima2">LIMA2</option>
                                                                        <option value="lima3">LIMA3</option>
                                                                        <option value="lujo">LUJO</option>
                                                                        <option value="machupicchu">MACHUPICCHU</option>
                                                                        <option value="mapi">MAPI</option>
                                                                        <option value="maras">MARAS</option>
                                                                        <option value="moray">MORAY</option>
                                                                        <option value="mpi2">MPI2</option>
                                                                        <option value="nasca">NASCA</option>
                                                                        <option value="playas-del-norte">PLAYAS DEL NORTE</option>
                                                                        <option value="portada">PORTADA</option>
                                                                        <option value="puerto-maldonado">PUERTO MALDONADO</option>
                                                                        <option value="puno">PUNO</option>
                                                                        <option value="trujillo">TRUJILLO</option>
                                                                        <option value="valle">VALLE</option>
                                                                        <option value="vinicunca">VINICUNCA</option>
                                                                    </select>

                                                                </div>
                                                                <div style="height: 170px; width: 170px" v-if="caja==true && loadingPrincipal==true">

                                                                </div>
                                                                <template v-if="imagePortada && q.withHeader !=''">
                                                                    <div class="d-flex align-items-center" v-if="imagePortada">

                                                                        <img class="showWithCover"
                                                                            :src="imagePortada"
                                                                            style="margin: 9px;width: 120px;height: auto;">
                                                                    </div>
                                                                    <!-- <div class="d-flex align-items-center">
                                                                        <img v-if="q.withHeader"
                                                                            :src="baseExternalURL + 'images/word/' + select_itinerary_with_cover + '.jpg'"
                                                                            style="margin: 9px;width: 120px;height: auto;">
                                                                    </div> -->
                                                                    <h5>{{
                                                                            translations.label.do_you_want_a_client_logo
                                                                        }}:</h5>
                                                                    <div class="d-flex align-items-center" @click.stop="clickDropDown($event)">
                                                                        <label class=" mx-3" @click.stop="">
                                                                            <input :disabled="loading"
                                                                                name="with_header_radio" type="radio"
                                                                                :value="1" v-model="q.withClientLogo"
                                                                                @change="setWithHeader(q)"> Sí
                                                                        </label>
                                                                        <label class=" mx-3" @click.stop="">
                                                                            <input :disabled="loading"
                                                                                name="with_header_radio" type="radio"
                                                                                :value="2"
                                                                                @change="setWithHeader(q)"
                                                                                v-model="q.withClientLogo"> No
                                                                        </label>

                                                                        <label class=" mx-3" @click.stop="">
                                                                            <input :disabled="loading"
                                                                                name="with_header_radio" type="radio"
                                                                                @change="setWithHeader(q)"
                                                                                :value="3" v-model="q.withClientLogo"> Ninguno
                                                                        </label>
                                                                    </div>
                                                                </template>
                                                                <!-- <div class="d-flex align-items-center">
                                                                    <select @click.stop=""
                                                                            v-model="select_itinerary_with_client_logo"
                                                                            v-if="q.withClientLogo">
                                                                        <option value="0000">PARACAS - RESERVA</option>
                                                                        <option value="0001">CHOQUEQUIRAO</option>
                                                                        <option value="0002">CRUCEROS - FRONT</option>
                                                                        <option value="0003">CRUCEROS - LADO</option>
                                                                        <option value="0004">CAÑÓN DEL COLCA</option>
                                                                        <option value="0005">FAMILIA EN CUSCO</option>
                                                                        <option value="0006">PARACAS - CANDELABRO
                                                                        </option>
                                                                        <option value="0020">MACHU PICCHU 1</option>
                                                                        <option value="0010">MACHU PICCHU 2</option>
                                                                        <option value="0011">MACHU PICCHU 3</option>
                                                                        <option value="0012">MACHU PICCHU 4</option>
                                                                        <option value="0013">ICA - TUBULARES</option>
                                                                        <option value="0015">CATARATAS DE GOCTA</option>
                                                                        <option value="0017">PUERTO MALDONADO 1</option>
                                                                        <option value="0033">PUERTO MALDONADO 2</option>
                                                                        <option value="0018">SALKANTAY</option>
                                                                        <option value="0019">VALLE SAGRADO 1</option>
                                                                        <option value="0022">VALLE SAGRADO 2</option>
                                                                        <option value="0021">HUILLOC</option>
                                                                        <option value="0023">CEVICHE 1</option>
                                                                        <option value="0028">CEVICHE 2</option>
                                                                        <option value="0024">MAÍZ MORADO</option>
                                                                        <option value="0025">HUAYNA PICCHU</option>
                                                                        <option value="0026">VINICUNCA</option>
                                                                        <option value="0027">LIMA</option>
                                                                        <option value="0029">OLLANTAYTAMBO 1</option>
                                                                        <option value="0030">OLLANTAYTAMBO 2</option>
                                                                        <option value="0031">NORTE</option>
                                                                        <option value="0032">PUNO - CHULLPAS DE
                                                                            SILLUSTANI
                                                                        </option>
                                                                        <option value="0034">TARAPOTO</option>
                                                                        <option value="0035">TRUJILLO</option>
                                                                        <option value="0037">TREE HOUSE LODGE</option>
                                                                    </select>
                                                                </div> -->
                                                                <!-- <div class="d-flex align-items-center">
                                                                    <img v-if="q.withClientLogo"
                                                                         :src="baseExternalURL + 'images/word_with_client_logo/' + select_itinerary_with_client_logo + '.jpg'"
                                                                         style="margin: 9px;width: 120px;height: auto;">
                                                                </div> -->
                                                                <div class="d-flex my-3" @click.stop="clickDropDown($event)">
                                                                    <button :disabled="loading"
                                                                            class="btn btn-secondary mx-1" type="button"
                                                                            @click.stop="q.showDownloadItinerary=false">
                                                                        {{ translations.label.cancel }}
                                                                    </button>
                                                                    <button :disabled="loading"
                                                                            class="btn btn-primary mx-1" type="button"
                                                                            @click.stop="downloadItinerary(q)">
                                                                        <i class="fa fa-spin fa-spinner"
                                                                           v-if="loading"></i> {{
                                                                            translations.label.download
                                                                        }}
                                                                    </button>
                                                                </div>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="javascript:;" class="acciones__item" @click="duplicate(q)"
                                               v-if="( q.categories.length>1 ||
                                                           ( q.categories.length==1 &&
                                                            ( q.categories[0].type_class.code!='x' && q.categories[0].type_class.code!='X' ) ) ) &&
                                                     ( class_market === '' && market !== '' ) ">
                                                    <span class="icon--acciones">
                                                        <i class="icon-copy" :title="translations.label.duplicate"
                                                           style="line-height: 0;"></i>
                                                    </span>
                                            </a>

                                          <span v-if="q.file_number">
                                          </span>
                                          <span v-else>
                                              <a href="javascript:;" class="acciones__item" @click="willRelationOrder(q)"
                                                 v-if="q.categories.length>1 ||
                                                             ( q.categories.length==1 &&
                                                              ( q.categories[0].type_class.code!='x' && q.categories[0].type_class.code!='X' ) ) &&
                                                       ( class_market != '' || market == '' ) "
                                                 :title="q.order_related">
                                                      <span class="icon--acciones">
                                                          <i class="icon-globe-switch" :title="q.order_related"
                                                             data-toggle="modal"
                                                             data-target="#modalOrders"
                                                             style="font-size: 28px; line-height: 0;"></i>
                                                      </span>
                                              </a>
                                          </span>

                                            <a href="javascript:;" class="acciones__item" @click="willShare(q)" v-if="q.categories.length>1 ||
                                                           ( q.categories.length==1 &&
                                                            ( q.categories[0].type_class.code!='x' && q.categories[0].type_class.code!='X' ) ) && user_type_id==3">
                                                    <span class="icon--acciones">
                                                        <i class="icon-send" :title="translations.label.share"
                                                           data-toggle="modal"
                                                           data-target="#modalEnviarCotizacion"></i>
                                                    </span>
                                            </a>

                                            <a href="javascript:;" class="acciones__item" @click="view_history(q)"
                                               v-if="q.history_logs_count>0">
                                                <span data-toggle="modal" data-target="#modalHistoryLogs">
                                                    <i class="fa fa-history"></i>
                                                    <span class="total-history">{{ q.history_logs_count }}</span>
                                                </span>
                                            </a>
                                            <a href="javascript:;" style="opacity: 0.5;" class="acciones__item" v-else>
                                                <span>
                                                    <i class="fa fa-history"></i>
                                                    <span class="total-history">{{ q.history_logs_count }}</span>
                                                </span>
                                            </a>

                                          <div v-if="q.file_number">
                                              <span v-for="order_ in file_orders_related" v-if="order_.file == q.file_number" class="text-danger font-weight-bold file-number file-number-a3">
                                                  Nro. Ped.: {{ order_.order }}
                                              </span>
                                          </div>
                                          <div v-if="q.reservation">
                                              <span v-for="order_ in file_orders_related" v-if="order_.file == q.reservation.file_code" class="text-danger font-weight-bold file-number file-number-a2">
                                                  Nro. Ped.: {{ order_.order }}
                                              </span>
                                          </div>

                                        </div>
                                    </div>
                                </div>
                                <hr v-if="q.editing_quote_user.editing">
                                <div class="col-md-12 p-0" v-if="q.editing_quote_user.editing">
                                    <p>
                                        {{ translations.label.edit_user }}
                                        <span
                                            class="font-weight-bold">({{ q.editing_quote_user.user.code }}) {{ q.editing_quote_user.user.name }}</span>
                                        {{ translations.label.edit_user_quote }}
                                    </p>
                                    <small class="mb-0">
                                        {{ translations.label.info_duplicate_user_quote }}
                                    </small>
                                </div>
                            </div>

                            <center v-if="quotes.length==0">{{ translations.label.no_quote_to_show }}</center>

                        </div>
                        <div v-if="!loading && quotes.length > 0">
                            <nav aria-label="page navigation">
                                <div class="text-center">
                                    <ul class="pagination">
                                        <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                            @click="setPage(1)">
                                            <a class="page-link" href="#">{{ translations.label.first_page }}</a>
                                        </li>

                                        <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                            @click="setPage(pageChosen-1)">
                                            <a class="page-link" href="#">{{ translations.label.previous }}</a>
                                        </li>

                                        <li v-for="(page, p) in quote_pages" @click="setPage(page)" v-if="show_pages[p]"
                                            :class="{'page-item':true,'active':(page==pageChosen) }">
                                            <a class="page-link" href="javascript:;">{{ page }}</a>
                                        </li>

                                        <li :class="{'page-item':true,'disabled':(pageChosen==quote_pages.length)}"
                                            @click="setPage(pageChosen+1)">
                                            <a class="page-link" href="#">{{ translations.label.next }}</a>
                                        </li>

                                        <li :class="{'page-item':true,'disabled':(pageChosen==quote_pages.length)}"
                                            @click="setPage(quote_pages.length)">
                                            <a class="page-link" href="#">{{ translations.label.last_page }}</a>
                                        </li>
                                    </ul>
                                </div>

                            </nav>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div id="modalAlerta" tabindex="1" role="dialog" class="modal" ref="modalAlerta">
            <div role="document" class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center">
                            <div class="icon">
                                <i class="icon-alert-circle" v-if="!loading"></i>
                                <i class="spinner-grow" v-if="loading"></i>
                            </div>
                            <strong v-if="!loading">{{ translations.label.you_are_about_to_replace }}!</strong>
                            <strong v-if="loading">{{ translations.label.loading }}</strong>
                        </h4>
                        <p class="text-center" v-if="!loading"><strong>{{
                                translations.label.we_suggest_you_save
                            }}.</strong></p>
                        <div class="group-btn" v-if="!loading">
                            <button type="button" @click="replaceQuote()" data-dismiss="modal"
                                    class="btn btn-secondary">{{ translations.label.replace }}
                            </button>
                            <button type="button" @click="goToQuotesFront()" data-dismiss="modal"
                                    class="btn btn-primary">{{ translations.label.save_first }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalWillRemove" tabindex="1" role="dialog" class="modal" ref="modalWillRemove">
            <div role="document" class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center">
                            <div class="icon">
                                <i class="icon-alert-circle" v-if="!loading"></i>
                                <i class="spinner-grow" v-if="loading"></i>
                            </div>
                            <strong v-if="!loading">{{ translations.label.one_step_away_eliminating_quote }}: "{{
                                    quoteChoosen.name
                                }}"</strong>
                            <strong v-if="loading">{{ translations.label.loading }}</strong>
                        </h4>
                        <p class="text-center" v-if="!loading"><strong>{{ translations.label.are_you_sure }}</strong>
                        </p>
                        <div class="group-btn" v-if="!loading">
                            <button type="button" data-toggle="modal" data-target="#modalWillRemove"
                                    class="btn btn-secondary">{{ translations.label.cancel }}
                            </button>
                            <button type="button" @click="remove()" data-toggle="modal" data-target="#modalWillRemove"
                                    class="btn btn-primary">{{ translations.label.yes_continue }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal--cotizacion modal--envios" id="modalHistoryLogs" tabindex="-1" role="dialog">
            <div class="modal-dialog modal--cotizacion__document" role="document">
                <div class="modal-content modal--cotizacion__content">
                    <div class="modal-header">
                        <button class="close" type="button" data-dismiss="modal"
                                aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal--cotizacion__header">
                            <h3 class="modal-title"><b>{{ translations.label.history_of_changes }} -
                                {{ translations.label.quote }} N° {{ quoteChoosen.id }} - "{{ quoteChoosen.name }}"</b>
                            </h3>
                            <div class="link-volver">
                                <div class="col-12 mt-4">
                                    <div class="row">
                                        <div class="col-sm-auto">
                                            <span class="modal-paragraph">
                                                <a href="#" data-toggle="modal" data-target="#modalHistoryLogs">
                                                 <b>{{ translations.label.back_to_my_quotes }}<i
                                                     class="icon-arrow-left"></i></b>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal--cotizacion__body">
                            <form class="form">

                                <div class="row no-gutters">

                                    <div class="form-group form-group__select col-sm-2 pr-2">
                                        <!--    custom-combo  -->
                                        <div class="">
                                            <select class="form-control" style="padding: 9px 12px;"
                                                    v-model="history_logs_filter_by"
                                                    @change="history_logs_change_filter_by()">
                                                <option selected value="">{{ translations.label.show_all }}</option>
                                                <option value="store">{{ translations.label.store }} ...</option>
                                                <option value="update">{{ translations.label.update }} ...</option>
                                                <option value="destroy">{{ translations.label.destroy }} ...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group input-group col-sm-4 pl-1 mr-auto">
                                        <span class="input-group-text"><i class="icon-search"></i></span>
                                        <input :disabled="loading" class="form-control" id="query_history_logs"
                                               v-model="query_history_logs"
                                               type="text" :placeholder="translations.label.filter_logs+'...'">
                                    </div>
                                </div>
                            </form>
                            <div class="tbl--cotizacion" v-if="!loading">
                                <div class="tbl--cotizacion__header">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__codigo">
                                                <h4 class="tbl--cotizacion__title">{{ translations.label.type }}</h4>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__detalles">
                                                <h4 class="tbl--cotizacion__title">{{ translations.label.user }}</h4>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__fecha">
                                                <h4 class="tbl--cotizacion__title">{{ translations.label.detail }}</h4>
                                            </div>
                                        </div>
                                        <div class="col px-6">
                                            <div class="tbl--cotizacion__ciudad">
                                                <h4 class="tbl--cotizacion__title">{{
                                                        translations.label.creation_date
                                                    }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tbl--cotizacion tbl--cotizacion__detalle">
                                    <div class="tbl--cotizacion__content" v-for="history in history_logs"
                                         v-if="history_logs.length>0">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col px-3">
                                                <div class="tbl--cotizacion__item tbl--cotizacion__codigo">
                                                    <span>
                                                        <i class="fa" :class="{'fa-trash':history.type==='destroy',
                                                        'fa-save':history.type==='store',
                                                        'fa-edit':history.type==='update'}"></i>
                                                        {{ translations.label[history.type] }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col px-3">
                                                <div class="tbl--cotizacion__item tbl--cotizacion__fecha"><span>
                                                <b>{{ history.user.name }}</b></span></div>
                                            </div>
                                            <div class="col px-3">
                                                <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                                <span>
                                                    {{ translations.messages[history.slug] }}.
                                                    <b v-if="history.slug==='update_accommodation' ||
                                                             history.slug==='update_markup' ||
                                                             history.slug==='update_name' ||
                                                             history.slug==='update_service_type_general' ||
                                                             history.slug==='update_general_adults' ||
                                                             history.slug==='update_type_pax' ||
                                                             history.slug==='update_date_general' ||
                                                             history.slug==='update_general_childs' ||
                                                             history.slug==='update_date_estimated' ||
                                                             history.slug==='copy_category' ||
                                                             history.slug==='store_general_adults'
                                                            ">
                                                        {{ history.previous_data }} {{
                                                            translations.label.to
                                                        }} {{ history.current_data }}
                                                    </b>
                                                    <b v-if="history.slug==='destroy_range'
                                                            ">
                                                        "{{ history.previous_data }}"
                                                    </b>
                                                    <b v-if="history.slug==='destroy_category' ||
                                                            history.slug==='store_category'
                                                            ">
                                                        "{{ history.current_data }}"
                                                    </b>
                                                    <b v-if="history.slug==='destroy_service' ||
                                                            history.slug==='store_service'||
                                                            history.slug==='store_extension'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        <i class="fa fa-calendar"></i> {{
                                                            history.current_data_json.date_in
                                                        }}
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{ history.current_data_json.service_code }}
                                                    </b>
                                                    <b v-if="history.slug==='store_flight'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        <i class="fa fa-calendar"></i> {{
                                                            history.current_data_json.date_in
                                                        }}
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{
                                                            history.current_data_json.origin
                                                        }} -> {{ history.current_data_json.destiny }}
                                                    </b>
                                                    <b v-if="history.slug==='replace_service'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        <i class="fa fa-calendar"></i> {{
                                                            history.current_data_json.date_in
                                                        }}
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{
                                                            history.previous_data_json.service_code
                                                        }} {{ translations.label.to }}
                                                        {{ history.current_data_json.service_code }}
                                                    </b>
                                                    <b v-if="history.slug==='update_date'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{ history.current_data_json.service_code }}
                                                        <i class="fa fa-calendar"></i>
                                                        {{ history.previous_data }} {{
                                                            translations.label.to
                                                        }} {{ history.current_data_json.date_in }}
                                                    </b>
                                                    <b v-if="history.slug==='update_service_paxs'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{ history.current_data_json.service_code }}
                                                        <i class="fa fa-calendar"></i>
                                                        (ADL:{{
                                                            history.previous_data_json.adult
                                                        }}, CHD:{{ history.previous_data_json.child }})
                                                        {{
                                                            translations.label.to
                                                        }} (ADL:{{
                                                            history.current_data_json.adult
                                                        }}, CHD:{{ history.current_data_json.child }})
                                                    </b>
                                                    <b v-if="history.slug==='update_occupation'
                                                            ">
                                                        {{ translations.label.category }}:
                                                        {{ history.current_data_json.quote_category_name }},
                                                        {{
                                                            translations.label[history.current_data_json.type_service]
                                                        }}:
                                                        {{ history.current_data_json.service_code }}
                                                        <i class="fa fa-calendar"></i>
                                                        (SGL:{{
                                                            history.previous_data_json.single
                                                        }}, DBL:{{
                                                            history.previous_data_json.double
                                                        }}, TPL:{{ history.previous_data_json.triple }})
                                                        {{
                                                            translations.label.to
                                                        }} (SGL:{{
                                                            history.current_data_json.single
                                                        }}, DBL:{{
                                                            history.current_data_json.double
                                                        }}, TPL:{{ history.current_data_json.triple }})
                                                    </b>
                                                </span>
                                                </div>
                                            </div>
                                            <div class="col px-6">
                                                <div class="tbl--cotizacion__item tbl--cotizacion__ciudad">
                                                    {{ history.created_at | reformatDateTime }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <nav aria-label="page navigation" v-if="history_logs.length>0">
                                        <ul class="pagination">
                                            <li :class="{'page-item':true,'disabled':(history_logs_page_chosen==1)}"
                                                @click="history_set_page(history_logs_page_chosen-1)">
                                                <a class="page-link" href="#">{{ translations.label.previous }}</a>
                                            </li>

                                            <li v-for="page in history_logs_quote_pages" @click="history_set_page(page)"
                                                :class="{'page-item':true,'active':(page==history_logs_page_chosen) }">
                                                <a class="page-link" href="javascript:;">{{ page }}</a>
                                            </li>

                                            <li :class="{'page-item':true,'disabled':(history_logs_page_chosen==history_logs_quote_pages.length)}"
                                                @click="history_set_page(history_logs_page_chosen+1)">
                                                <a class="page-link" href="#">{{ translations.label.next }}</a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="tbl--cotizacion__content" v-if="history_logs.length==0">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col px-12 text-center">
                                                {{ translations.label.no_registration }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tbl--cotizacion" v-if="loading">
                                <center><i class="spinner-grow"></i></center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal--cotizacion" id="modalEnviarCotizacion" tabindex="-1" role="dialog">
            <div class="modal-dialog modal--cotizacion__document" role="document">
                <div class="modal-content modal--cotizacion__content">
                    <div class="modal-header">
                        <button class="close" type="button" data-dismiss="modal"
                                aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal--cotizacion__header">
                            <h3 class="modal-title"><b>{{ translations.label.share }} {{ translations.label.quote }}</b>
                            </h3>

                            <div class="link-volver">
                                <div class="col-12 mt-4">
                                    <div class="row">
                                        <div class="col-sm-auto">
                                        <span class="modal-paragraph">
                                            <a href="#" data-toggle="modal" data-target="#modalEnviarCotizacion"
                                               id="hidden-modal-share">
                                                <b>{{ translations.label.to_return_to }} {{
                                                        translations.label.my_quotes
                                                    }} <i
                                                        class="icon-arrow-left"></i></b>
                                            </a>
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class=" modal--cotizacion__body">
                            <div class="tbl--cotizacion tbl--cotizacion__detalle">
                                <div :class="'tbl--cotizacion__content tbl--cotizacion__' + quoteChoosen.backRow">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-1">
                                            <a href="#"
                                               class="tbl--cotizacion__item tbl--cotizacion__delete text-center">
                                                &nbsp;
                                            </a>
                                        </div>
                                        <div class="col-1">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__codigo">
                                                <span>#{{ quoteChoosen.id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                                <span>
                                                    <strong>
                                                        {{ quoteChoosen.name }}
                                                    </strong>
                                                </span>
                                                <span>{{
                                                        translations.label.created
                                                    }}: {{ quoteChoosen.created_at | formatDate }}</span>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__fecha">
                                                <span><b>{{ quoteChoosen.date_in | reformatDate }}</b></span>
                                                <span>({{ quoteChoosen.when_it_starts }})</span></div>
                                        </div>
                                        <div class="col-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__ciudad">
                                                <span class="tag" v-for="destiny in quoteChoosen.destinations">
                                                    {{ destiny?.state?.iso || 'NO ISO' }}
                                                </span>
                                                <span v-if="quoteChoosen.destinations.length==0">-</span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__duracion text-center">
                                                <span>{{ quoteChoosen.nights }}</span><small>{{
                                                    translations.label.nights
                                                }}</small></div>
                                        </div>
                                        <div class="col-1">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__tipo text-center">
                                                <span>
                                                    {{ quoteChoosen.service_type.code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row col-12" v-if="quoteChoosen.shared == 1">
                                <h3>{{ translations.label.shared_with }}: ({{ quoteChoosen.permission.client.code }})
                                    {{ quoteChoosen.permission.seller.name }} {{
                                        quoteChoosen.permission.created_at
                                    }}</h3>
                            </div>
                            <div class="row col-12" v-if="quoteChoosen.shared == 0">
                                <div class="form-group col-3">
                                    <select name="" id="" v-model="marketSelected" @change="getClientsMarket(undefined)"
                                            class="form-control select_quote">
                                        <option value="">{{ translations.label.select_market }}</option>
                                        <option :value="market.id" v-for="market in markets_modal_share">{{
                                                market.name
                                            }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-5">
                                    <v-select :options="clients_market" v-model="clientsSelected"
                                              @input="getClientSellers" class="form-control select_quote"></v-select>
                                </div>
                                <div class="form-group col-4">
                                    <select class="form-control select_quote" v-model="clientSellerSelected">
                                        <option value="">{{ translations.label.select_seller }}</option>
                                        <option :value="seller" v-for="seller in client_sellers">
                                            <div v-if="seller.name == ''">
                                                {{ seller.email }}
                                            </div>
                                            <div v-if="seller.name != ''">
                                                {{ seller.name }}
                                            </div>
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row col-12" v-if="quoteChoosen.shared == 0">
                                <div class="form-group form-group__select col-sm-3 pr-3">
                                    <div class="input-group">
                                        <input type="radio" id="uno" value="view_permission"
                                               v-model="permission_selected">
                                        <label for="uno" class="label_radio_format">{{
                                                translations.label.display_only
                                            }}</label>
                                        <br>
                                        <input type="radio" id="Dos" value="edit_permission"
                                               v-model="permission_selected">
                                        <label for="Dos" class="label_radio_format">{{
                                                translations.label.edit_mode
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row col-12" v-if="quoteChoosen.shared == 0">
                                <div class="form-group col-sm-auto">
                                    <button class="btn btn-primary" type="button" @click="share()"
                                            :disabled="loading">
                                        <i class="fa fa-spinner fa-spin" v-if="loading"></i>
                                        {{ translations.label.share }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>
<style>
.file-number{
  max-width: max-content;
  padding: 0 5px;
}
.file-number-a3{
  background: cornsilk;
}
.file-number-a2{
  background: #d5ffe0;
}
.v-select.with-icon .vs__actions {
    display: flex!important;
    padding: 0!important;
}
.total-history {
    font-size: 9px;
    display: block;
    margin-top: -36px;
    margin-right: -26px;
    font-weight: 900;
}

.navbar .aurora-main .dropdown-menu .nav-primary > .nav-item {
    margin-bottom: 15px;
}

.cursor-pointer {
    cursor: pointer;
}

.select_quote {
    border: 1px darkgray solid;
}

.back-success {
    background-color: #98ffc0 !important;
}

.back-success-2 {
    background-color: #d6ffe6 !important;
}

.label_radio_format {
    padding: 5px 9px !important;
    line-height: 3px;
}

.pagination {
    max-height: 85px;
    overflow-y: auto;
    margin-left: 8px;
}
</style>
<script>

export default {
    data: () => {
        return {
            loadingPrincipal:false,
            loading: false,
            isLoadingUsers: false,
            quotes: [],
            file_orders_related: [],
            type_classes: [],
            service_types: [],
            categoriesForView: [],
            servicesForView: [],
            quoteChoosen: {
                destinations: [],
                service_type: {
                    code: '',
                },
            },
            totals: {},
            filterUserType: 'E',
            filterBy: 'all',
            query_quotes: '',
            pageChosen: 1,
            limit: 3,
            quote_pages: [],
            formSend: {
                name: '',
                date_in: '',
            },
            optionsR: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
            },
            timesPost: 0,
            language_for_download: 'es',
            filter_by_destiny: [],
            checkedAllDestinations: false,
            refPax: '',
            translations: {
                label: {},
                validations: {},
                messages: {},
            },
            select_itinerary_with_cover: 'amazonas',
            select_itinerary_with_client_logo: '',
            market: '',
            client: '',
            markets: [],
            executive: '',
            send_notification_shared: 0,
            executives: [],
            markets_modal_share: [],
            marketSelected: '',
            clientsSelected: '',
            clientSellerSelected: '',
            clients_market: [],
            user_type_id: null,
            user_id: null,
            permission_selected: 'view_permission',
            client_sellers: [],
            more_client_sellers: [],
            class_market: "",
            baseExternalURL: "",
            history_logs: [],
            query_history_logs: '',
            history_logs_page_chosen: 1,
            history_logs_limit: 5,
            history_logs_quote_pages: [],
            history_logs_filter_by: '',
            show_pages: [],
            idCliente:'',
            imagePortada:'',
            urlPortada:'',
            portadaName:'',
            caja:false,
            updateImage:false,
            textoCliente:'',
            all_status: [],
        };
    },
    created() {

    },
    mounted() {
        this.user_id = localStorage.getItem('user_id')
        this.user_type_id = localStorage.getItem('user_type_id')

        moment.locale(localStorage.getItem('lang'))

        this.baseExternalURL = baseExternalURL

        this.setTranslations();

        if(this.user_type_id == 4)
        {
            this.executive = parseInt(this.user_id)
        }

        this.$root.$on('reloadQuotes', (payload) => {
            this.searchQuotes();
        });

        let search_quotes = document.getElementById('query_quotes');
        let timeout_quotes;
        search_quotes.addEventListener('keydown', () => {
            clearTimeout(timeout_quotes);
            timeout_quotes = setTimeout(() => {
                this.pageChosen = 1;
                this.searchQuotes();
                clearTimeout(timeout_quotes);
            }, 1000);
        });

        let history_logs_search_quotes = document.getElementById('query_history_logs')
        let history_logs_timeout_quotes
        history_logs_search_quotes.addEventListener('keydown', () => {
            clearTimeout(history_logs_timeout_quotes)
            history_logs_timeout_quotes = setTimeout(() => {
                this.history_logs_page_chosen = 1
                this.filter_history_logs()
                clearTimeout(history_logs_timeout_quotes)
            }, 1000)
        })

        this.searchQuotes();
        this.searchDestinations();
        this.searchTypeClasses();
        this.searchServiceTypes();
        if (this.user_type_id == 3) {
            this.getMarkets()
        }
        if (this.user_type_id == 4) {
            this.get_more_sellers()
        }

    },
    methods: {
        backMiniMenu(q) {
            $('.showDownloadSkeleton').css('display', 'none')
            $('.showDownloadItinerary').css('display', 'none')
            $('.miniMenu').css('display', 'block')
            q.showDownloadSkeleton = false
            q.showDownloadItinerary = false
        },
        downloadDropdown(q){

            this.backMiniMenu(q);
            this.updateImage = false;
            this.textoCliente= ""

        },
        clickDropDown(e){
            console.log("infrso")
            e.stopPropagation();


        },
        validatePagination: function () {
            this.view_pages = 15
            let page = this.pageChosen
            let pages = this.quote_pages.length

            for (let p = 0; p < pages; p++) {
                this.show_pages[p] = false

                if (page < this.view_pages) {
                    if (this.view_pages > 0) {
                        this.view_pages -= 1
                        this.show_pages[p] = true
                    }
                } else {
                    if (page >= (pages - (this.view_pages) / 2)) {
                        if (p >= (pages - this.view_pages)) {
                            this.show_pages[p] = true
                        }
                    } else {
                        if (p >= parseFloat(page - parseFloat(this.view_pages / 2)) && p <= parseFloat(page + parseFloat(this.view_pages / 2))) {
                            this.show_pages[p] = true
                        }
                    }
                }
            }

        },
        filter_history_logs() {
            this.loading = true
            axios.get(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quoteChoosen.id + '/history_logs?query=' + this.query_history_logs +
                '&page=' + this.history_logs_page_chosen + '&limit=' + this.history_logs_limit + '&filter_by=' +
                this.history_logs_filter_by + '&lang=' + localStorage.getItem('lang'))
                .then(response => {
                    this.history_logs = response.data.data
                    this.history_logs_quote_pages = []
                    for (let i = 0; i < (response.data.count / this.history_logs_limit); i++) {
                        this.history_logs_quote_pages.push(i + 1)
                    }
                    this.loading = false
                }).catch(error => {
                this.loading = false
                console.log(error)
            })
        },
        verify_seller(seller_user_id) {
            let n = 0
            this.more_client_sellers.forEach((s) => {
                if (s.id === seller_user_id) {
                    n++
                }
            })
            return (n > 0) ? true : false
        },
        get_more_sellers: function () {
            let vm = this
            axios.get('api/sellers/more?lang=' + localStorage.getItem('lang') + '&status=1')
                .then((response) => {
                    vm.more_client_sellers = response.data.data

                    vm.more_client_sellers.forEach((_seller, s) => {
                        vm.$set(_seller, 'label', _seller.code + ' - ' + _seller.name)
                    })
                })
        },
        getClientSellers: function (value) {
            axios.get('api/sellers?lang=' + localStorage.getItem('lang') + '&client_id=' + value.code + '&status=1').then((response) => {
                this.client_sellers = response.data.data
            })
        },
        getMarkets: function () {
            this.executive = ''
            axios.get('api/markets/modal/share')
                .then((response) => {
                    this.markets_modal_share = []
                    response.data.forEach((m) => {
                        if (m.belongs_user) {
                            this.markets_modal_share.push(m)
                        }
                    })
                    this.markets = response.data
                }).catch((e) => {
                console.log(e)
            })
        },
        getClientsMarket: function (_market) {
            if (_market != undefined) {
                this.client = ''
                axios.post('api/get/clients/market', {market_id: _market})
                    .then((response) => {
                        this.clients_market = response.data
                    }).catch((e) => {
                    console.log(e)
                })
            } else {
                if (this.marketSelected != "") {
                    this.clientsSelected = ''
                    axios.post('api/get/clients/market', {market_id: this.marketSelected})
                        .then((response) => {
                            this.clients_market = response.data
                        }).catch((e) => {
                        console.log(e)
                    })
                }
            }
        },

        setWithClientLogo: function (q) {

            if (!q.withHeader) {
                q.withClientLogo = 4
                this.imagePortada="";
                this.urlPortada='';

                } else {
                    q.withClientLogo = 3
                    this.loadingPrincipal = true
                    this.imagePortada='';
                    this.idCliente = localStorage.getItem('client_id')
                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                        params:{
                            clienteId:this.idCliente,
                            portada:this.select_itinerary_with_cover,
                            portadaName:q.name,
                            estado:3,
                            refPax:this.refPax,
                            lang:localStorage.getItem('lang'),
                            nameCliente:this.refPax,
                        }
                    }).then((result) => {

                        console.log(result.data)
                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loadingPrincipal = false
                        this.urlPortada = result.data.image + '.jpg';

                    });

                    q.withClientLogo = 3
                }
            if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){

                this.updateImage = true
                this.textoCliente = this.refPax

            }
        },
        setWithHeader: function (q) {
           // q.withHeader = false

           if (q.withClientLogo==1) {
                this.imagePortada = ''
                this.loadingPrincipal = true
                this.caja=true
                this.idCliente = localStorage.getItem('client_id')
                this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                    params:{
                        clienteId:this.idCliente,
                        portada:this.select_itinerary_with_client_logo,
                        portadaName:q.name,
                        estado:q.withClientLogo,
                        refPax:this.refPax,
                        lang:localStorage.getItem('lang'),
                        nameCliente: this.refPax,
                    }
                }).then((result) => {

                    this.imagePortada = window.a3BaseQuoteServerURL +  result.data.image + '.jpg'
                    this.caja=false
                    this.loadingPrincipal = false
                    this.urlPortada = result.data.image + '.jpg';
                });

                q.withHeader = true


            } else if(q.withClientLogo==2){

                q.withHeader = true
                this.loadingPrincipal = true
                this.caja=true
                this.imagePortada='';
                this.idCliente = localStorage.getItem('client_id')

                axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                    params:{
                        clienteId:this.idCliente,
                        portada:this.select_itinerary_with_cover,
                        portadaName:q.name,
                        estado:q.withClientLogo,
                        refPax:this.refPax,
                        lang:localStorage.getItem('lang'),
                        nameCliente:this.refPax,
                    }
                }).then((result) => {

                    this.imagePortada = window.a3BaseQuoteServerURL +  result.data.image + '.jpg'
                    this.caja=false
                    this.loadingPrincipal = false
                    this.urlPortada = result.data.image + '.jpg';

                });

            }else{
                this.imagePortada = ''
                this.select_itinerary_with_client_logo = ""
                q.withHeader = true

                this.loadingPrincipal = true
                this.caja=true
                this.idCliente = localStorage.getItem('client_id')

                axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                    params:{
                        clienteId:this.idCliente,
                        portada:this.select_itinerary_with_cover,
                        portadaName:q.name,
                        estado:q.withClientLogo,
                        refPax:this.refPax,
                        lang:localStorage.getItem('lang'),
                        nameCliente:this.refPax,
                    }
                }).then((result) => {

                    this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                    this.caja=false
                    this.loadingPrincipal = false
                    this.urlPortada = result.data.image + '.jpg';

                });

            }

            if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){

                this.updateImage = true
                this.textoCliente = this.refPax

            }

        },

        async setComboPortada(quote_open){

                    if(quote_open.withClientLogo==3){
                        this.loadingPrincipal = true
                        this.caja = true
                        this.imagePortada='';
                        this.idCliente = localStorage.getItem('client_id')

                        await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                            params:{
                                clienteId:this.idCliente,
                                portada:this.select_itinerary_with_cover,
                                portadaName:quote_open.name,
                                estado:quote_open.withClientLogo,
                                refPax:this.refPax,
                                lang:localStorage.getItem('lang'),
                                nameCliente:this.refPax,
                            }
                        }).then((result) => {

                            this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                            this.caja = false
                            this.loadingPrincipal = false
                            this.urlPortada = result.data.image + '.jpg';

                        });

                    }else if(quote_open.withClientLogo==1){

                        this.loadingPrincipal = true
                        this.caja = true
                        this.imagePortada='';
                        this.idCliente = localStorage.getItem('client_id')
                        this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                        await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                            params:{
                                clienteId:this.idCliente,
                                portada:this.select_itinerary_with_client_logo,
                                portadaName:quote_open.name,
                                estado:quote_open.withClientLogo,
                                refPax:this.refPax,
                                lang:localStorage.getItem('lang'),
                                nameCliente:this.refPax,
                            }
                        }).then((result) => {

                            this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                            this.caja = false
                            this.loadingPrincipal = false
                            this.urlPortada = result.data.image + '.jpg';


                        });
                    }else if(quote_open.withClientLogo==2){

                        this.loadingPrincipal = true
                        this.caja = true
                        this.imagePortada='';
                        this.idCliente = localStorage.getItem('client_id')
                        this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                        await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate',{
                            params:{
                                clienteId:this.idCliente,
                                portada:this.select_itinerary_with_client_logo,
                                portadaName:quote_open.name,
                                estado:quote_open.withClientLogo,
                                refPax:this.refPax,
                                lang:localStorage.getItem('lang'),
                                nameCliente:this.refPax,
                            }
                        }).then((result) => {

                            this.imagePortada = window.a3BaseQuoteServerURL +  result.data.image + '.jpg'
                            this.caja = false
                            this.loadingPrincipal = false
                            this.urlPortada = result.data.image + '.jpg';

                        });

                    }

                    if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){

                        this.updateImage = true
                        this.textoCliente = this.refPax

                    }

                },


        getLanguageName(usersModalShare) {
            if (usersModalShare === null) {
                return '';
            } else {
                return usersModalShare.language.name;
            }
        },
        getUserTypeDescription(usersModalShare) {
            if (usersModalShare === null) {
                return '';
            } else {
                return usersModalShare.userType.description;
            }
        },
        setTranslations() {
            axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/quote').then((data) => {
                this.translations = data.data;

                this.all_status = [
                    { code: 'all', label: this.translations.label.show_all },
                    { code: 'activated', label: this.translations.label.only + ' ' + this.translations.label.active },
                    { code: 'expired', label: this.translations.label.only + ' ' + this.translations.label.expired },
                    { code: 'comingExpired', label: this.translations.label.next_to_expire },
                    { code: 'received', label: this.translations.label.received },
                    { code: 'sent', label: this.translations.label.sent }
                ]
            });
        },
        getRouteExport: function (quote) {
            let client_id = localStorage.getItem('client_id');
            if (quote.operation === 'ranges') {
                return window.a3BaseQuoteServerURL + 'quote/' + quote.id + '/export/ranges?lang=' +
                    localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' +
                    localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id');
            }
            if (quote.operation === 'passengers') {
                return window.a3BaseQuoteServerURL + 'quote/' + quote.id + '/export/passengers?lang=' +
                    localStorage.getItem('lang') + '&client_id=' + client_id + '&user_id=' +
                    localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id');
            }

        },
        toggleDestiny(destiny) {
            destiny.checked = !(destiny.checked);
        },
        toggleAllDestinations() {
            this.filter_by_destiny.forEach(c => {
                c.checked = this.checkedAllDestinations;
            });
        },
        searchDestinations() {
            axios.get(window.a3BaseQuoteServerURL + 'api/quote/ubigeo/selectbox/destinations').then((result) => {
                if (result.data.success) {
                    result.data.data.forEach(d => {
                        d.checked = false;
                    });
                    this.filter_by_destiny = result.data.data;
                }
            }).catch((e) => {
                this.$toast.error('Error: ' + e, {
                    position: 'top-right',
                });
            });
        },
        willDownloadSkeleton(quote) {
            if (quote.categories.length == 0) {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }
            this.refPax = localStorage.getItem('client_name');
            quote.showDownloadSkeleton = true;
            quote.showDownloadItinerary = false;
        },
        willDownloadItinerary(quote) {

            if (quote.categories.length == 0) {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }

            this.urlPortada = ''

            this.refPax = localStorage.getItem('client_name');
            quote.showDownloadItinerary = true;
            quote.showDownloadSkeleton = false;

            if(quote.showDownloadItinerary = true){
                quote.withHeader = true
                quote.withClientLogo=3
                this.language_for_download = localStorage.getItem('lang')

                this.setComboPortada(quote)
            }
        },
        downloadSkeleton(quote) {

            if (this.refPax == '') {
                this.$toast.error(this.translations.validations.rq_pax_reference, {
                    position: 'top-right',
                });
                return;
            }

            if (quote.radioCategories == '') {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }

            let client_id = localStorage.getItem('client_id');
            if (!client_id) {
                this.$toast.error(this.translations.validations.rq_client, {
                    position: 'top-right',
                });
                return;
            }
            this.loading = true;
            // PASAR CLIENTE, BOLEAN DE ENCABEZADO
            axios({
                method: 'GET',
                url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/skeleton?lang=' +
                    this.language_for_download +
                    '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&refPax=' + this.refPax,
                responseType: 'blob',
            }).then((response) => {
                this.loading = false;
                var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                var fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'Skeleton - ' + quote.name + '.docx');
                document.body.appendChild(fileLink);

                fileLink.click();

            }).catch(() => {
                this.loading = false;
                this.enabledBtnExcel = false;
                this.$toast.error(this.translations.messages.internal_error, {
                    position: 'top-right',
                });
            });
        },
        async downloadItinerary(quote) {

            if (this.refPax == '') {
                this.$toast.error(this.translations.validations.rq_pax_reference, {
                    position: 'top-right',
                });
                return;
            }

            if (quote.radioCategories == '') {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }

            let client_id = localStorage.getItem('client_id');
            if (!client_id) {
                this.$toast.error(this.translations.validations.rq_client, {
                    position: 'top-right',
                });
                return;
            }

            if (quote.withHeader == '' && quote.withClientLogo == '') {
                this.$toast.error(this.translations.validations.rq_check_portada, {
                    position: 'top-right'
                })
                return
            }

            // PASAR CLIENTE, BOLEAN DE ENCABEZADO
            if(this.urlPortada!=''){

                this.loadingPrincipal = true;
                // if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){
                //     await this.setComboPortada(quote)
                //     this.loadingPrincipal = true;
                // }
                if(((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() && this.updateImage == false ) ||
                    (this.refPax.trim()).toUpperCase()  !== (this.textoCliente.trim()).toUpperCase() && this.updateImage == true){
                    await this.setComboPortada(quote)
                    this.loadingPrincipal = true;
                }

                await axios({
                    method: 'GET',
                    url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/itinerary?lang=' +
                        this.language_for_download +
                        '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&cover=' +
                        this.select_itinerary_with_cover + '&refPax=' + this.refPax +
                        '&client_logo=' + quote.withClientLogo +
                        '&cover_client_logo=' + this.select_itinerary_with_client_logo
                        + '&urlPortadaLogo=' + this.urlPortada ,
                    responseType: 'blob',
                }).then((response) => {

                    // console.log(response.data)

                    this.loadingPrincipal = false;
                    var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                    var fileLink = document.createElement('a');
                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', 'Itinerary - ' + quote.name + '.docx');
                    document.body.appendChild(fileLink);

                    fileLink.click();

                    this.backMiniMenu(quote)


                }).catch(() => {
                    this.loadingPrincipal = false;
                    this.enabledBtnExcel = false;
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right',
                    });
                });
            }else{

                this.loadingPrincipal = true;
                // if((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() ){
                //     await this.setComboPortada(quote)
                //     this.loadingPrincipal = true;
                // }
                if(((this.refPax.trim()).toUpperCase()  !== (localStorage.getItem('client_name').trim()).toUpperCase() && this.updateImage == false ) ||
                    (this.refPax.trim()).toUpperCase()  !== (this.textoCliente.trim()).toUpperCase() && this.updateImage == true){
                    await this.setComboPortada(quote)
                    this.loadingPrincipal = true;
                }
                await axios({
                    method: 'GET',
                    url: window.a3BaseQuoteServerURL + 'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/itinerary?lang=' + this.language_for_download +
                        '&client_id=' + client_id + '&use_header=' + quote.withHeader + '&cover=' + this.select_itinerary_with_cover + '&refPax=' + this.refPax
                        + '&client_logo=' + quote.withClientLogo,
                        responseType: 'blob',
                }).then((response) => {

                        this.loadingPrincipal = false
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'Itinerary - ' + quote.name + '.docx')
                        document.body.appendChild(fileLink)

                        fileLink.click()

                        this.backMiniMenu(quote)

                    }).catch((e) => {
                    this.loadingPrincipal = false
                    this.enabledBtnExcel = false
                    console.log(e.message)
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right'
                    })
                })
            }
        },
        share() {

            if (this.clientsSelected == '' || this.marketSelected == '' || this.clientSellerSelected == '') {
                this.$toast.error(this.translations.validations.rq_data, {
                    position: 'top-right',
                });
                return;
            }
            this.send_notification_shared = 1;
            this.loading = true;
            let data = {
                clients_selected: this.clientsSelected,
                client_seller_selected: this.clientSellerSelected,
                send_notification: this.send_notification_shared,
                permission_selected: this.permission_selected
            };

            axios.post(window.baseExternalURL + 'api/quote/' + this.quoteChoosen.id + '/share/quote', data).then((result) => {
                this.send_notification_shared = 0;
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.quote_shared_correctly, {
                        position: 'top-right',
                    });

                    this.modalShareToggle();
                    this.searchQuotes();
                } else {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right',
                    });
                }
                this.loading = false;
            }).catch((e) => {
                this.$toast.error('Error: ' + e, {
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        view_history(q) {
            this.quoteChoosen = q;
            this.history_logs_filter_by = ''
            this.history_logs_page_chosen = 1
            this.filter_history_logs()
        },
        willShare(q) {
            // this.searchUsersInit();
            this.quoteChoosen = q;
            this.formSend.name = q.name;
            this.formSend.date_in = moment(q.date_in).format('DD/MM/Y');
        },
        willRelationOrder(q) {
            this.quoteChoosen = q;
            this.$root.$emit('updateQuoteChoosen', q);
        },
        changeFilterBy() {
            this.pageChosen = 1;
            this.searchQuotes();
        },
        history_logs_change_filter_by() {
            this.history_logs_page_chosen = 1;
            this.filter_history_logs();
        },
        setPage(page) {
            if (page < 1 || page > this.quote_pages.length) {
                return;
            }
            this.pageChosen = page;
            this.searchQuotes(page);
        },
        history_set_page(page) {
            if (page < 1 || page > this.history_logs_quote_pages.length) {
                return;
            }
            this.history_logs_page_chosen = page;
            this.filter_history_logs();
        },
        remove() {
            this.loading = true;
            axios.delete(window.a3BaseQuoteServerURL + 'api/quotes/' + this.quoteChoosen.id).then((result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.successfully_removed, {
                        position: 'top-right',
                    });
                    this.searchQuotes();
                } else {
                    if (result.data.message == 'editing') {
                        this.$toast.error(this.translations.validations.cannot_be_deleted_in_edition, {
                            position: 'top-right',
                        });
                    } else {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right',
                        });
                    }
                }
                this.loading = false;
            }).catch((e) => {
                console.log(e);
                this.loading = false;
            });

        },
        willRemove(q) {
            this.quoteChoosen = q;
        },
        duplicate(q) {
            this.loading = true;
            axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + q.id + '/copy/quote').then((result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.quotation_doubled_correctly, {
                        position: 'top-right',
                    });
                    this.searchQuotes();
                } else {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right',
                    });
                    this.loading = false;
                }
            }).catch((e) => {
                this.$toast.error('Error: ' + e, {
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        async putQuote() {
            this.loading = true;
            axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quoteChoosen.id + '/copy/quote', {
                status: 2,
                client_id: localStorage.getItem('client_id'),
            }).then(async (result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.quote_in_edit_mode, {
                        // override the global option
                        position: 'top-right',
                    });
                    localStorage.setItem("modal_edit_new_quote_id", result.data.new_quote_id);
                    this.goToQuotesFront();
                } else {
                    this.$toast.error(this.translations.messages.internal_error, {
                        position: 'top-right',
                    });
                    this.loading = false;
                }
            }).catch((e) => {
                this.$toast.error('Error: ' + e, {
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        goToQuotesFront() {
            window.location.href = '/packages/cotizacion';
        },
        replaceQuote() {
            this.loading = true;
            axios.post(window.a3BaseQuoteServerURL + 'api/quote/' + this.quoteChoosen.id + '/replaceQuoteInFront',
                {client_id: localStorage.getItem('client_id')}).then((result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.quote_in_edit_mode, {
                        // override the global option
                        position: 'top-right',
                    });
                    localStorage.setItem("modal_edit_new_quote_id", result.data.quote_front.id);
                    this.goToQuotesFront();
                } else {
                    this.$toast.error(this.translations.messages.internal_error, {
                        // override the global option
                        position: 'top-right',
                    });
                    this.loading = false;
                }
            }).catch((e) => {

                this.$toast.error('Error: ' + e, {
                    // override the global option
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        modalAlertToggle() {
            let el = document.getElementById('hidden-modal-alert');
            el.click();
        },
        modalShareToggle() {
            let el = document.getElementById('hidden-modal-share');
            el.click();
        },
        edit(q) {
            this.modalAlertToggle();
            this.quoteChoosen = q;
            this.loading = true;
            axios.get(window.a3BaseQuoteServerURL + 'api/quote/existByUserStatus/2').then((result) => {
                if (result.data.success) {
                    this.loading = false;
                } else {
                    this.putQuote();
                }
            }).catch((e) => {
                console.log(e);
                this.loading = false;
            });

        },
        willEdit(q) {
            axios.get(window.a3BaseQuoteServerURL + 'api/quote/check_editing/' + q.id).then((result) => {
                q.editing_quote_user.editing = result.data.editing
                if (result.data.editing) {
                    q.editing_quote_user.user = result.data.user
                } else {
                    q.editing_quote_user.user = null
                    console.log(q)
                    this.edit(q)
                }
            }).catch((e) => {
                console.log(e);
                this.loading = false;
            });
        },
        toggleTabCategory(categ) {
            this.loading = true;
            this.categoriesForView.forEach(c => {
                c.tabActive = '';
            });
            categ.tabActive = 'active';
            this.servicesForView = categ.services;
            this.loading = false;
        },
        viewServices(q) {
            this.categoriesForView = [];
            this.servicesForView = [];
            axios.get(window.a3BaseQuoteServerURL + 'api/quote/' + q.id + '/categories/services?lang=' +
                localStorage.getItem('lang')).then((result) => {
                result.data.forEach(categ => {
                    categ.tabActive = '';
                });
                this.categoriesForView = result.data;
                this.servicesForView = this.categoriesForView[0].services;
                this.categoriesForView[0].tabActive = 'active';
            }).catch((e) => {
                console.log(e);
                this.loading = false;
            });
        },
        changeMarket: function () {
            this.executive = '';
            let vm = this;

            // if class_market != '' || market == '' -- Puede editar / agregar

            this.class_market = ''
            this.markets.forEach((m) => {
                if (m.id === this.market && (m.belongs_user)) {
                    this.class_market = 'back-success-2'
                }
            })

            setTimeout(function () {
                vm.getClientsMarket(vm.market)
                vm.searchQuotes();
            }, 10);
        },
        searchQuotes: function (_page) {
            this.loading = true;

            if(this.executive == null)
            {
                this.executive = ''
            }

            if (_page == undefined) {
                this.pageChosen = 1
            }

            let _destinations = [];
            this.filter_by_destiny.forEach(d => {
                if (d.checked) {
                    _destinations.push(d.id);
                }
            });

            axios.get(window.a3BaseQuoteServerURL + 'api/quotes?lang=' + localStorage.getItem('lang') +
                '&page=' + this.pageChosen + '&limit=' + this.limit +
                '&filterBy=' + this.filterBy + '&queryCustom=' + this.query_quotes +
                '&filterUserType=' + this.filterUserType +
                '&destinations=' + _destinations + '&market=' + this.market + '&client=' + ((this.client.code != undefined) ? this.client.code : '') + '&executive=' + this.executive).then((result) => {
                result.data.data.forEach(q => {
                    q.showForAdd = false;
                    q.showDownloadSkeleton = false;
                    q.showDownloadItinerary = false;
                    q.withHeader = true;
                    q.withClientLogo = false;
                    q.radioCategories = (q.categories.length > 0) ? q.categories[0].id : '';
                });

                this.executives = Object.values(result.data.executives);
                // console.log(this.executives);
                this.quotes = result.data.data;
                this.file_orders_related = result.data.orders_related;
                this.totals = result.data.totals;
                this.quote_pages = [];
                for (let i = 0; i < (result.data.count / this.limit); i++) {
                    this.quote_pages.push(i + 1);
                }
                this.loading = false;
                this.validatePagination()
            }).catch((e) => {
                console.log(e);
            });
        },
        searchTypeClasses: function () {
            axios.get(baseExternalURL + 'api/typeclass/quotes/selectbox?lang=' + localStorage.getItem('lang')).then((result) => {
                result.data.data.forEach(_c => {
                    if (_c.code != 'X' && _c.code != 'x') {
                        _c.check = false;
                        this.type_classes.push(_c);
                    }
                });
            }).catch((e) => {
                console.log(e);
            });
        },
        searchServiceTypes: function () {
            axios.get(baseExternalURL + 'api/service_types/selectBox?lang=' + localStorage.getItem('lang')).then((result) => {
                result.data.data.forEach(_c => {
                    if (_c.code != 'NA') {
                        this.service_types.push(_c);
                    }
                });
            }).catch((e) => {
                if (e.response.status === 401) {
                    document.getElementById('logout-form').submit()
                }
                console.log(e);
            });
        },
        importServices(quote) {
            quote.loadingRow = true;
            axios.post('api/quote/' + quote.id + '/import', {code: quote.code}).then((result) => {
                quote.loadingRow = false;
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.imported_correctly, {
                        position: 'top-right',
                    });
                    this.searchQuotes();
                } else {
                    this.$toast.error(result.data.message, {
                        position: 'top-right',
                    });
                }
            }).catch((e) => {
                this.$toast.error(this.translations.messages.internal_error, {
                    position: 'top-right',
                });
                quote.loadingRow = false;
                console.log(e);
            });
        },
        replaceMultiple(quote_id, category_id) {

            let _type_classes = [];
            this.type_classes.forEach(_tc => {
                if (_tc.check) {
                    _type_classes.push(_tc.id);
                }
            });

            if (_type_classes.length == 0) {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }
            this.loading = true;
            // quote/{quote_id}/category/{category_id}/replaceMultiple
            axios.post(window.a3BaseQuoteServerURL +  'api/quote/' + quote_id + '/category/' + category_id + '/replaceMultiple',
                {type_classes: _type_classes, client_id: localStorage.getItem('client_id')}).then((result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.saved_correctly, {
                        position: 'top-right',
                    });
                    this.searchQuotes();
                } else {
                    this.$toast.error('Error', {
                        position: 'top-right',
                    });
                }
                this.loading = false;
            }).catch((e) => {

                this.$toast.error('Error' + e, {
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        willAdd(quote) {
            if (quote.categories.length == 0) {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }
            if (quote.categories.length == 1) {
                this.add(quote);
            } else {
                quote.showForAdd = true;
            }
        },
        add(quote) {

            if (quote.radioCategories == '') {
                this.$toast.error(this.translations.validations.rq_category, {
                    position: 'top-right',
                });
                return;
            }

            this.loading = true;

            axios.post(window.a3BaseQuoteServerURL +  'api/quote/' + quote.id + '/category/' + quote.radioCategories + '/add',
                {client_id: localStorage.getItem('client_id')}).then((result) => {
                if (result.data.success) {
                    this.$toast.success(this.translations.messages.successfully_added, {
                        position: 'top-right',
                    });
                    this.goToQuotesFront();
                } else {
                    if (result.data.type && result.data.type == 'empty') {
                        this.$toast.error(this.translations.messages.try_to_edit_a_quote_first, {
                            position: 'top-right',
                        });
                    } else {
                        this.$toast.error('Error', {
                            position: 'top-right',
                        });
                    }
                }
                this.loading = false;
            }).catch((e) => {

                this.$toast.error('Error' + e, {
                    position: 'top-right',
                });
                this.loading = false;
            });
        },
        changeTypeClass(c) {
            c.check = c.check;
        },
        formatDate: function (_date, charFrom, charTo, orientation) {
            _date = _date.split(charFrom);
            _date =
                (orientation)
                    ? _date[2] + charTo + _date[1] + charTo + _date[0]
                    : _date[0] + charTo + _date[1] + charTo + _date[2];
            return _date;
        },
    },
    filters: {
        formatDate: function (_date) {
            if (_date == undefined) {
                return;
            }
            let secondPartDate = '';

            if (_date.length > 10) {
                secondPartDate = _date.substr(10, _date.length);
                _date = _date.substr(0, 10);
            }

            _date = _date.split('-');
            _date = _date[2] + '/' + _date[1] + '/' + _date[0];
            return _date + secondPartDate;
        },
        reformatDate: function (_date) {
            if (_date == undefined) {
                return;
            }
            _date = moment(_date).format('ddd D MMM YYYY');
            return _date;
        },
        reformatDateTime: function (_date) {
            if (_date == undefined) {
                return;
            }
            _date = moment(_date).format('ddd D MMM YYYY hh:mm:ss');
            return _date;
        },
    },
};
</script>
