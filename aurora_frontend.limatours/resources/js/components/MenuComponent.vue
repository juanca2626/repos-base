<template>
    <div>
        <loading-component v-show="blockPage"></loading-component>

        <nav class="navbar navbar-expand-lg">

            <a class="navbar-brand" href="/">
                <img src="/images/logo/logo_nav.jpg">
            </a>
            <div class="dropdown aurora-main">
                <button class="dropdown-toggle" type="button" id="dropdownMain" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    Aurora <i class="icon-menu" v-if="!user_invited"></i>
                </button>
                <div class="dropdown dropdown-menu" v-if="!user_invited" aria-labelledby="dropdownMain">
                    <div class="nav nav-primary">
                        <hr style="width: 90%;">
                        <hr v-if="client_id!=''" style="width: 90%;">
                        <div class="nav-item"
                             v-if="hasPermission('mfhotels','read') || hasPermission('mfservices','read') || hasPermission('mfpackages','read') || (user_type_id == 3)">
                            <span class="nav-item-title"><i class="icon-calendar-confirm"></i>{{ translations.label.online }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfhotels','read') || (user_type_id == 3)">
                                    <a class="nav-link" href="/hotels">{{ translations.label.hotels }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfservices','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/services">{{ translations.label.services }}</a>
                                </li>

<!--                                v-if="rol==='neg' || rol==='admin'"-->
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelHotel(2022)"
                                       v-if="client_id !=''">
                                       {{ translations.label.download_hotels_rates }} 2022
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelHotel(2023)"
                                       v-if="client_id != ''">
                                       {{ translations.label.download_hotels_rates }} 2023
                                   </a>
                                </li> -->
<!--                                <li class="nav-item">-->
<!--                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelHotel(2024)"-->
<!--                                       v-if="client_id != ''">-->
<!--                                       {{ translations.label.download_hotels_rates }} 2024-->
<!--                                   </a>-->
<!--                                </li>-->

                                <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelHotel(2026)"
                                       v-if="client_id != ''">
                                       {{ translations.label.download_hotels_rates }} 2026
                                   </a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelServiceYear(2022)"
                                       v-if="client_id != ''">
                                       {{ translations.label.download_rate_services_2022 }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelServiceYear(2023)"
                                       v-if="client_id != ''">
                                       {{ translations.label.download_rate_services }} 2023
                                    </a>
                                </li> -->
<!--                                <li class="nav-item">-->
<!--                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelServiceYear(2024)"-->
<!--                                       v-if="client_id != ''">-->
<!--                                       {{ translations.label.download_rate_services }} 2024-->
<!--                                    </a>-->
<!--                                </li>-->

                                <li class="nav-item">
                                    <a class="nav-link" href="#" @click.prevent="getRouteExcelServiceYear(2026)"
                                       v-if="client_id != ''">
                                       {{ translations.label.download_rate_services }} 2026
                                    </a>
                                </li>
                                <!--                                <li class="nav-item"-->
                                <!--                                    v-if="hasPermission('mftrains','read') || (client_id!='' && user_type_id == 3)">-->
                                <!--                                    <a class="nav-link" href="/trains">{{ translations.label.trains }}</a>-->
                                <!--                                </li>-->
                                <li class="nav-item"
                                    v-if="hasPermission('mfpackages','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/packages">{{
                                        translations.label.packages }}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/master-sheets"
                                       v-if="hasPermission('mastersheet','read') || (client_id!='' && user_type_id == 3)"
                                       class="nav-link ">
                                        {{ translations.label.master_sheet }}
                                    </a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfquotationboard','read') || (client_id!='' && user_type_id == 3)">
                                    <a href="/packages/cotizacion" class="nav-link ">
                                        {{ translations.label.quotation_board }}
                                    </a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfdestinationservices','read') && (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="https://destinationservices.bokun.io/extranet/login"
                                       target="_blank">Destination Services</a>
                                </li>
                            </ul>
                        </div>
                        <!--Inicio Stela-->
<!--                        <div class="nav-item"-->
<!--                             v-show="(hasPermission('mffilesmanagementstela','read') || hasPermission('mftrackingstela','read') || hasPermission('mfcheckpaymentsupplier','read') || hasPermission('mfobservedaccountingdocumentsstela','read') || hasPermission('mfunlockfilestela','read') || hasPermission('mfadminsalesestela','read')) && (user_type_id == 3)">-->
<!--                            <span class="nav-item-title"><i class="icon-calendar-confirm"></i>Stela</span>-->
<!--                            <ul class="nav nav-secondary flex-column">-->
<!--                                <li class="nav-item" v-if="hasPermission('mffilesmanagementstela','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/litt0160?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.files_management }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="nav-item" v-if="hasPermission('mftrackingstela','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/litt1030?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.tracking_programation }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="nav-item" v-if="hasPermission('mfcheckpaymentsupplier','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/litt1530?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.check_payments_supplier }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="nav-item" v-if="hasPermission('mfobservedaccountingdocumentsstela','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/litt1570?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.observed_accounting_documents }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="nav-item" v-if="hasPermission('mfunlockfilestela','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/turdesb?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.unlockfile }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="nav-item" v-if="hasPermission('mfadminsalesestela','read')">-->
<!--                                    <a class="nav-link"-->
<!--                                       :href="'http://192.168.250.20:8200/wa/r/litt0150?Arg='+code+'&Arg=5&Arg=aurora&Arg=kslajdbaslkbd&Arg=kabskjbkasbjsa%20bkcjbaskub873y82y8y81hh88r83i'"-->
<!--                                       target="_blank">{{ translations.label.adminsales }}-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </div>-->
                        <!--Fin Stela-->
                        <!--Inicio Files-->
                        <div class="nav-item"
                             v-if="hasPermission('mffilesa3query','read') || hasPermission('mffilesquery','read') || hasPermission('mfproductnoconforming','read') || hasPermission('mfquadfiles','read') || hasPermission('mfservicetracking','read')|| hasPermission('mfexecutiveboard','read') || hasPermission('mfreport','read') || (user_type_id == 3)">
                            <span class="nav-item-title"><i
                                class="icon-settings"></i>{{ translations.label.files }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item"
                                    v-if="(user_type_id == 3) && hasPermission('managefilesms','management') ">
                                    <a class="nav-link" :href="goToA3('files')">{{ translations.label.my_files_query }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mffilesquery','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/consulta_files">{{ translations.label.files_query }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfquadfiles','read') && (user_type_id == 3)">
                                    <a class="nav-link" href="/reports/files">{{ translations.label.file_quad }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfservicetracking','read') && (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" target="_blank" v-bind:href="'https://extranet.litoapps.com/migration/' +
                                        'monitoreo.php?u='+code+'&t=u&l='+lang">{{ translations.label.service_tracking }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfproductnoconforming','read') && (user_type_id == 3)">
                                    <a class="nav-link" target="_blank"
                                        v-bind:href="goToA3('accountancy/sig-tracking-platform/non-conforming-products')">
                                        {{ translations.label.product_no_conforming }}
                                    </a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfclaim','read') && (user_type_id == 3)">
                                    <a class="nav-link" target="_blank"
                                    v-bind:href="goToA3('accountancy/customer-service/claims')">
                                        {{ translations.label.claim }}
                                    </a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfcongratulation','read') && (user_type_id == 3)">
                                    <a class="nav-link" target="_blank"
                                        v-bind:href="goToA3('accountancy/sig-tracking-platform/congratulations')">
                                        {{ translations.label.congratulation }}
                                    </a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfexecutiveboard','read') && (user_type_id == 3)">
                                    <a class="nav-link" href="/board">{{ translations.label.executive_board }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfreport','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/reportes-reservas">{{ translations.label.reports }}</a>
                                </li>
                                <li class="nav-item"
                                    v-if="hasPermission('mfstadisticcharts','read') || (user_type_id == 3)">
                                    <a class="nav-link" href="/reports">{{ translations.label.stadistic_charts }}</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Files-->
                        <!--Inicio Multimedia-->
                        <div class="nav-item"
                             v-if="hasPermission('mfphotos','read') || hasPermission('mfvideos','read') || hasPermission('mfjournals','read') || (user_type_id == 3)">
                            <span class="nav-item-title"><i class="icon-calendar-confirm"></i>{{ translations.label.multimedia }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item"
                                    v-if="hasPermission('mfphotos','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/fotos">{{ translations.label.photos }}</a>
                                </li>
                                <!-- li class="nav-item"
                                    v-if="hasPermission('mfvideos','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/videos">{{ translations.label.videos }}</a>
                                </li -->
                                <!-- li class="nav-item"
                                    v-if="hasPermission('mfjournals','read') || (client_id!='' && user_type_id == 3)">
                                    <a class="nav-link" href="/noticias">{{ translations.label.journals }}</a>
                                </li -->
                                <li class="nav-item" v-if="user_id == 1320 || user_id==693 || user_id==2883 || user_id==3590
                                        || user_id==2726 || user_id==2476 || user_id==2483 || user_id==2709 || user_id==3357
                                        || user_id==2766 || user_id==2866 || user_id==2767 || user_id==3167 || user_id==2844
                                        || user_id==2758 || user_id==2883 || user_id==2773 || user_id==2770">
                                    <a class="nav-link" href="/multimedia/hotels/report">Reporte de hoteles
                                        cloudinary</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Multimedia-->
                        <!-- Inicio Users -->
                        <div class="nav-item" v-if="(hasPermission('mfusuariostom','read') || hasPermission('incacalendar','read') || hasPermission('mfcustomercard','read') || hasPermission('mforderreports','read')) && (user_type_id == 3)">
                            <span class="nav-item-title"><i
                                class="icon-settings"></i>{{ translations.label.users }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfusuariostom','read')">
                                    <a class="nav-link" href="/users">{{ translations.label.usersTOM }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mforderreports','read')">
                                    <a class="nav-link" href="/report_orders">{{ translations.label.order_reports }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfcustomercard','read')">
                                    <a class="nav-link" href="/customers/card">{{ translations.label.customer_card
                                        }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('incacalendar','read')">
                                    <a class="nav-link" href="/calendario_inca">Calendario Inca</a>
                                </li>
                            </ul>
                        </div>
                        <!-- Fin Users -->
                        <!--Inicio Cental ordenes-->
                        <div class="nav-item" v-if="(hasPermission('mfdestinationservices','read') ||
                                hasPermission('mfmyorders','read') ||
                                hasPermission('mforderreports','read')) && (user_type_id == 3)">
                            <span class="nav-item-title"><i class="icon-settings"></i>{{ translations.label.order_center }}</span>
                            <ul class="nav nav-secondary flex-column">
<!--                                <li class="nav-item" v-if="hasPermission('mfmyorders','read')">-->
<!--                                    <a class="nav-link" href="/orders">{{ translations.label.my_orders }}</a>-->
<!--                                </li>-->
                                <li class="nav-item" v-if="hasPermission('mfmyorders','read')">
                                    <a class="nav-link" :href="goToA3('order-control')">{{ translations.label.my_orders }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfdashboard','read')">
                                    <a class="nav-link" href="/dashboard">{{ translations.label.dashboard }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfreports','read')">
                                    <a class="nav-link" href="/reports/orders">{{ translations.label.reports_orders
                                        }}</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin central ordenes-->
                        <!-- Inicio Billing Report -->
                        <div class="nav-item"
                             v-if="(hasPermission('mfbillingreport','read') || hasPermission('mfproductivityreport','read')) && (user_type_id == 3)">
                            <span class="nav-item-title"><i class="icon-settings"></i>{{ translations.label.billings }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfbillingreport','read')">
                                    <a class="nav-link" href="/billing_report">{{ translations.label.billing_report
                                        }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfproductivityreport','read')">
                                    <a class="nav-link" href="/productivity_report">{{
                                        translations.label.productivity_report }}</a>
                                </li>
                            </ul>
                        </div>
                        <!-- Fin billing report -->
                        <!--Inicio OTS-->
                        <div class="nav-item"
                             v-if="hasPermission('mfcentralots','read') && (user_type_id == 3)">
                            <span class="nav-item-title"><i class="fas icon-folder"></i>OTS</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfcentralots','read')">
                                    <a class="nav-link" href="/central_bookings/tourcms">{{ translations.label.central
                                        }} OTS</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin OTS-->
                        <!--Inicio Facile-->
                        <div class="nav-item"
                             v-if="(hasPermission('mfprogramation','read') || hasPermission('mfconfirmationlist','read')) && ((user_type_id == 3))">
                            <span class="nav-item-title"><i class="icon-folder"></i>Facile</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfprogramation','read')">
                                    <a class="nav-link" href="/programacion">{{ translations.label.programation }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfconfirmationlist','read')">
                                    <a class="nav-link" href="/lista_confirmacion">
                                        {{ translations.label.confirmation_list }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Facile-->
                        <!--Inicio Masi-->
                        <div class="nav-item"
                             v-if="hasPermission('mfconfigurationmasi','read')">
                            <span class="nav-item-title"><i class="fas fa-robot"></i>Masi</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('masimailing','read')">
                                    <a class="nav-link" href="/masi_mailing">Configuración de correos y horarios</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('masistatistics','read')">
                                    <a class="nav-link" href="/masi_statistics">Estadísticas</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('masilogs','read')">
                                    <a class="nav-link" href="/masi_logs">Correos de Prueba - Logs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" target="_blank"
                                       v-bind:href="'https://masi.pe/login?token=' + access_token">
                                       Configuración - ChatBot
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Masi-->
                        <!--Inicio Help desk-->
                        <div class="nav-item" v-if="(user_type_id == 3) || user_type_id == 4">
                            <span class="nav-item-title"><i class="fas fa-life-ring"></i>{{ translations.label.helpdesk }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" target="_blank"
                                       href="#" @click.prevent="openSupportDesk">{{
                                        translations.label.helpdesk }}</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Help desk-->
                        <!--Inicio Cosig Reports-->
                        <div class="nav-item" v-if="hasPermission('mfstatclients','read') || hasPermission('mfreportcosig','read')">
                            <span class="nav-item-title"><i class="fas fa-life-ring"></i>{{ translations.label.cosig_reports }}</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item" v-if="hasPermission('mfstatclients','read')">
                                    <a class="nav-link" href="/stats">{{ translations.label.stat_clients }}</a>
                                </li>
                                <li class="nav-item" v-if="hasPermission('mfreportcosig','read')">
                                    <a class="nav-link"
                                       href="/reports/cosig">{{
                                        translations.label.cosig_reports }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link"
                                       href="/reports/cosig">Report - Recursos</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Cosig Reports-->
                        <!--Inicio Recursos Reports-->
                        <div class="nav-item">
                            <span class="nav-item-title"><i class="fas fa-life-ring"></i>Reportes - Recursos</span>
                            <ul class="nav nav-secondary flex-column">
                                <li class="nav-item">
                                    <a class="nav-link"
                                       href="/stats/login">Accesos a A2</a>
                                </li>
                            </ul>
                        </div>
                        <!--Fin Recursos Reports-->
                    </div>
                </div>
            </div>
            <form class="form-inline mr-auto" style="visibility: hidden;" v-if="!user_invited">
                <i class="icon-search search-display" id="searchDisplay"></i>
                <div class="form-group">
                    <button class="btn btn-secondary" type="submit"><i class="icon-search"></i></button>
                    <input class="form-control" type="search" :placeholder="translations.label.search"
                           aria-label="Search">
                </div>
            </form>
            <div class="select_lang">
                <select class="custom-select" name="lang" id="lang" v-model="lang" @change="setLang()">
                    <option :value="language.iso" v-for="language in languages">{{ language.iso }}</option>
                </select>
            </div>
            <div class="cliente-menu" :class="{seleccionado : this.client_id  }" v-if="!user_invited" v-show="user_type_id == 3 && !disabled_search_clients">
                <v-select :options="clients"
                          @input="getDestiniesByClientIdOnChange"
                          :value="this.client_id"
                          v-model="clientSelect"
                          :filterable="false"
                          @search="onSearchClient"
                          :disabled="disabled_search_clients"
                          :placeholder="translations.label.select_a_customer"
                          autocomplete="true">
                </v-select>
            </div>
            <div class="text-white" :class="{seleccionado : this.client_id  }" v-if="!user_invited" v-show="user_type_id == 3 && disabled_search_clients">
                {{client_name}}
            </div>
            <!--        <button type="button" class="mailto btn btn-secondary"><i class="icon-mail"></i><span>1</span></button>-->
            <div class="dropdown recordatorio" v-if="!user_invited">
                <button class="dropdown-toggle" type="button" id="dropdownRecordatorio" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    <span class="count-recordatorio">{{ all_reminders }}</span>
                </button>
                <div class="dropdown dropdown-menu notificaciones-content" aria-labelledby="dropdownRecordatorio">
                    <button type="button" class="close" data-dismiss="dropdown" aria-label="Close">
                        <span aria-hidden="true" class="close"> {{ translations.label.close }} x</span>
                    </button>
                    <h2>{{ translations.label.reminder }}</h2>
                    <div>
                        <div class="form-content">
                            <form class="form">
                                <div class="form-row d-flex justify-content-between align-items-end">
                                    <div class="form-group mx-3">
                                        <input type="text" class="form-control name" v-model="titleReminder"
                                               :placeholder="translations.label.reminder_name"/>
                                    </div>
                                    <div class="form-group mx-3">
                                        <label>{{ translations.label.set_date }}</label>
                                        <div class="d-flex justify-content-between">
                                            <date-picker class="date mr-2"
                                                         v-model="feciniReminder"
                                                         :config="options"></date-picker>
                                            <date-picker class="date ml-2"
                                                         v-model="fecfinReminder"
                                                         :config="options"></date-picker>
                                        </div>
                                    </div>
                                    <div class="form-group mx-3">
                                        <label>{{ translations.label.send_to }}</label>
                                        <v-select multiple label="code" :reduce="users => users.code" :options="users"
                                                  v-model="usersReminder" @search="filterUsers"
                                                  class="form-control"></v-select>
                                    </div>
                                    <div class="form-group mx-3">
                                        <label for="messageReminder">{{ translations.label.message }}</label>
                                        <textarea class="form-control mensaje" v-model="messageReminder" rows="3"
                                                  :placeholder="translations.messages.enter_your_message"></textarea>
                                    </div>
                                    <div class="form-group mx-3">
                                        <div class="box-card">
                                            <div class="title">{{ translations.label.configuration }}</div>
                                            <b-form-group class="content text-center">
                                                <b-form-radio-group
                                                    v-model="typeReminder"
                                                    :options="optionsS"
                                                    name="type"
                                                    switches
                                                ></b-form-radio-group>
                                            </b-form-group>
                                            <div class="d-flex justify-content-between">
                                                <div class="form-group ">
                                                    <label for="priorityReminder">{{ translations.label.importance
                                                        }}</label>
                                                    <v-select :options="priorities"
                                                              :reduce="priorities => priorities.value" label="text"
                                                              v-model="priorityReminder"
                                                              class="form-control nivel"></v-select>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="hour">{{ translations.label.shipping_time }}</label>
                                                    <date-picker class="hora" v-model="hourReminder"
                                                                 :config="optionsT"></date-picker>
                                                </div>
                                            </div>
                                            <div class="form-group m-2">
                                                <button class="btn btn-primary" type="button" @click="saveReminder()">
                                                    {{ translations.label.program }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mx-3">
                                        <a href="javascript:;" v-on:click="showReminders()">{{
                                            translations.label.see_all_reminders }}
                                            ({{ all_reminders }})</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <div class="dropdown notifications" v-if="!user_invited">
                <button class="dropdown-toggle" type="button" id="dropdownMail" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="icon-mail"></i>
                    <span class="count-notications">{{ notifications.all }}</span>
                </button>
                <div class="dropdown dropdown-menu notificaciones-content" aria-labelledby="dropdownMail">
                    <button type="button" class="close" data-dismiss="dropdown" aria-label="Close">
                        <span aria-hidden="true" class="close"> {{ translations.label.close }} x</span>
                    </button>
                    <h2>{{ translations.label.notifications }}</h2>
                    <span class="text-muted">{{ translations.label.you_have }} {{ notifications.news }} {{ translations.label.new_notifications }}.</span>
                    <div v-for="notification in notifications.items">
                        <div class="mt-3">
                            <div class="avatar" v-if="notification.photo != '' && notification.photo != null">
                                <img v-bind:src="'/images/users/' + notification.photo" alt=""/>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5><strong>{{ notification.title }}</strong></h5>
                                    <p v-if="notification.type == 1">
                                        <a v-bind:href="notification.url" target="_blank"
                                           v-on:click="updateNotification(notification.id)">
                                            {{ notification.content }}
                                        </a>
                                    </p>
                                    <p v-if="notification.type == 2">
                                        <a href="javascript:;"
                                           v-on:click="_showNotification(notification.module, notification.url, notification.id, notification.data)">
                                            {{ notification.content }}
                                        </a>
                                    </p>
                                    <span
                                        class="text-muted">{{ notification.user }} - {{ notification.created_at }}</span>
                                </div>
                                <div class="p-3">
                                    <i v-bind:class="['fas', 'fa-circle', (notification.status == 1) ? 'min-7' : 'min-0']"></i>
                                </div>
                            </div>
                        </div>
                        <hr class="ml-1 mr-1">
                    </div>
                </div>
            </div>
            <div class="dropdown cart cotizacion" v-if="!user_invited">
                <button type="button" id="dropdownCoti" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false"
                        class="dropdown-toggle"><i class="icon-folder"></i> <span class="count">{{ popup_quotes_totals.no_viewed }}</span>
                </button>
                <div aria-labelledby="dropdownCoti" class="dropdown dropdown-menu dropdown-menu-right">
                    <div class="dropdown-menu_header">
                        <div class="row">
                            <div class="col-12 text-right"></div>
                        </div>
                    </div>
                    <div class="dropdown-menu_body">
                        <h4 class="dropdown-menu_title"><b>{{ translations.label.my_quotes }}</b></h4>
                        <small class="dropdown-menu_subtitle">{{ translations.label.you_have }} {{
                            popup_quotes_totals.total }} {{ translations.label.quotes_on_your_worktable }}.
                        </small>
                        <ul class="list-group scrollbar-outer mt-4">
                            <li class="list-group-item" v-for="q in popup_quotes" :class="'back-'+q.backRow">
                                <div class="row no-gutters">
                                    <div class="col-2">
                                        <p><strong>{{ q.id }}</strong></p>
                                    </div>
                                    <div class="col-10">
                                        <p><strong>{{ q.name }}</strong></p>
                                        <span><small>{{ translations.label.start }}: {{ q.date_in | reformatDate }}</small></span><span><small
                                        style="color:#CE3B4D;"> ({{ q.when_it_starts }})</small></span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown-menu_footer">
                        <div class="row mt-4 justify-content-end">
                            <div class="col-5">
                                <a href="#" class="btn btn-primary btn-cotizacion" data-toggle="modal"
                                   data-target="#modalCotizaciones" @click="updateShowInPopup">{{
                                    translations.label.see_quotes }}</a>
                                <!--data-toggle="modal" data-target="#modalEnviarCotizacion"-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dropdown cart" v-if="!user_invited">
                <button class="dropdown-toggle" type="button" id="dropdownMain2" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="icon-shopping-cart"></i>
                    <span class="count">{{cart.quantity_items}}</span>
                </button>

                <div class="dropdown dropdown-menu carito-vacio" aria-labelledby="dropdownMain2"
                     v-if="cart.hotels.length == 0 && cart.services.length == 0">
                    <p>{{ translations.label.empty_cart }}</p>
                </div>
                <div class="dropdown dropdown-menu menu-cart" aria-labelledby="dropdownMain2"
                     v-if="cart.hotels.length > 0 || cart.services.length > 0">

                    <h2>{{ translations.label.your_shopping_cart }}.</h2>
                    <h3>{{ translations.label.you_have }} {{ (cart.hotels.length + cart.services.length) }} {{
                        translations.label.product_in_your_cart }}.</h3>
                    <div class="shopping-cart">
                        <div class="scroll-cart scrollbar-project">
                            <div class="card-body">

                                <div :id="'hotel-content-shopping'+index" class="hotel-content-shopping"
                                     v-for="(hotel,index) in cart.hotels">

                                    <div class="img-shopping">
                                        <img :src="hotel.hotel.galleries[0]">
                                    </div>
                                    <div class="content-shopping">
                                        <span class="tipo">{{ hotel.hotel.class }}</span>
                                        <h3 class="text-left">
                                            {{ hotel.hotel_name }}
                                            <span class="icon-star"></span>
                                            <div class="price">$<b>{{ hotel.total_hotel }}</b></div>
                                        </h3>
                                        <div class="date-shopping">
                                            <i class="icon-calendar"></i>
                                            <span>{{ formatDate(hotel.date_from) }}</span>
                                            <span>{{ formatDate(hotel.date_to) }}</span>

                                            <div class="total-rooms">

                                                <b-button v-b-toggle="'hotels-'+index">
                                                    <span class="fa fa-circle" v-for="room in hotel.rooms"></span>
                                                    {{ hotel.rooms.length }} {{ translations.label.room_abrev }}
                                                    <i class="icon-chevron-down"></i>
                                                </b-button>

                                            </div>
                                        </div>
                                        <b-collapse :id="'hotels-'+index">
                                            <b-card>
                                                <div class="car-room" v-for="room in hotel.rooms">
                                                    <h5>
                                                        <span class="fa fa-circle"></span> {{ room.room_name }}
                                                        <span class="text">{{ room.rate_name }}</span>
                                                        <button class="btn btn-success" v-if="room.onRequest ==1"
                                                                style="border-radius: 20px;">OK
                                                        </button>
                                                        <button class="btn btn-danger" v-if="room.onRequest ==0"
                                                                style="border-radius: 20px;">RQ
                                                        </button>
                                                    </h5>
                                                    <div class="price">
                                                        $ <b>{{ room.total_room }}</b>
                                                        <a class="remove" @click="cancelRoomsCart(room,hotel)">
                                                            <i class="icon-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </b-card>
                                        </b-collapse>
                                    </div>

                                </div>
                                <div :id="'service-content-shopping'+index" class="hotel-content-shopping"
                                     v-for="(service,index) in cart.services">

                                    <div class="img-shopping">
                                        <img v-if="service.service.galleries.length > 0"
                                             :src="service.service.galleries[0]">
                                    </div>
                                    <div class="content-shopping">
                                        <h3 class="text-left">
                                            <div style="width: 340px; font-weight: 600; font-size: 1.4rem;">
                                                {{ service.service_name }} - [{{service.service.code}}]
                                            </div>
                                            <div class="price">$<b>{{ service.total_service }}</b></div>
                                        </h3>
                                        <div class="car-room">
                                            <div class="price" style="z-index: 10001;">
                                                <a class="remove" @click="cancelServiceCart(service)">
                                                    <i class="icon-trash-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="date-shopping">
                                            <span class="ml-0" v-for="multiservicio in service.service.components">
                                                <span class="ml-0 icon-folder-plus"
                                                      v-if="service.service.components.length>0"></span>
                                                [{{ multiservicio.code }}] {{ multiservicio.descriptions.name }}
                                                <br>
                                            </span>
                                            <div class="mt-2">
                                                <i class="icon-calendar"></i>
                                                <span>{{ formatDate(service.date_from) }}</span><br>
                                                <i class="icon-map-pin"></i>
                                                <span> {{ service.service.origin.state }}</span>
                                                <span v-if="service.service.origin.city !== null"> {{ service.service.origin.city }}</span>
                                                <span v-if="service.service.origin.zone !== null"> {{ service.service.origin.zone }}</span>
                                                <i class="icon-arrow-right"></i>
                                                <span> {{ service.service.destiny.state }}</span>
                                                <span v-if="service.service.destiny.city !== null"> {{ service.service.destiny.city }}</span>
                                                <span v-if="service.service.destiny.zone !== null"> {{ service.service.destiny.zone }}</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="no-gutters total">
                            <h3>{{ translations.label.total_to_pay }} xxxx</h3>
                            <div class="price">USD <b>{{ cart.total_cart }}</b></div>
                        </div>
                    </div>

                    <a class="btn btn-primary btn-car" href="javascript:void(0)"  @click="goCartDetails()">{{ translations.label.go_to_cart }}</a>
                    <a class="btn btn-primary" href="javascript:void(0)"  @click="clearCart()">{{ translations.label.clear_cart }}</a>
                </div>
            </div>

            <div class="dropdown user-menu" :class="{'force-right':(user_invited)}">
                <button class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="icon-user" v-if="photo == '' || photo == null"></i>
                    <img class="rounded-circle" v-if="photo != '' && photo != null"
                         style="max-width:38px;max-height:38px;" v-bind:src="photo"/>
                </button>
                <div class="dropdown-menu dropdown-menu-right mi-cuenta" aria-labelledby="dropdownMenuButton">
                    <!--<a class="dropdown-item" href="#">Cambiar Usuario</a>
                    <a class="dropdown-item" href="#">Mi Contraseña</a>-->
                    <div class="text-center bloque-info">
                        <p>{{ translations.label.hi }}, <span class="">{{ username }}</span></p>
                        <div class="avatar" v-if="photo != '' && photo != null" v-show="!user_invited">
                            <img class="rounded-circle" v-bind:src="photo"/>
                        </div>
                        <div class="avatar" v-show="user_invited">
                            <img class="rounded-circle" :src="baseURL + 'images/anonimo.jpg'"/>
                        </div>

                    </div>
                    <div class="bloque-tabs mt-3">
                        <a class="tab d-flex justify-content-center" href="/account" v-if="!user_invited">
                            <i class="icon-settings"></i>
                            <p>{{ translations.label.my_account }}</p>
                        </a>
                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            <input type="hidden" name="_token" v-model="csrf_token">
                        </form>
                        <span class="tab d-flex justify-content-center"
                           @click="logout">
                            <i class="icon-user-x"></i>
                            <p>{{ translations.label.sign_off }}</p>
                        </span>
                    </div>
                </div>
            </div>
        </nav>
        <modal-quotes></modal-quotes>
        <modal-imports></modal-imports>
        <modal-orders></modal-orders>

        <b-modal id="my-modal" v-model="modal_reminders" size="lg" hide-footer
                 class="calender-modal modal-recordatorio">
            <div class="d-block" style="margin:-20px;">
                <div>
                    <h2><i class="icon-file-text"></i> {{ translations.label.reminders }} ({{ all_reminders }})</h2>
                </div>
                <div v-if="loadingModal">
                    <div class="alert alert-warning mt-3 mb-3">
                        {{ translations.label.loading }} ..
                    </div>
                </div>
                <div v-if="!loadingModal && all_reminders > 0">
                    <table class="table mt-5">
                        <thead class="p-0">
                        <tr>
                            <th></th>
                            <th class="">
                                {{ translations.label.actions }}
                            </th>
                            <th class="" nowrap>
                                {{ translations.label.reminder }}
                            </th>
                            <th class="" nowrap>
                                {{ translations.label.shipping_type }}
                            </th>
                            <th class="" nowrap>
                                {{ translations.label.start_date }}
                            </th>
                            <th class="" nowrap>
                                {{ translations.label.ending_date }}
                            </th>
                            <th class="" nowrap>
                                {{ translations.label.assigned_users }}
                            </th>
                        </tr>
                        </thead>
                        <tbody class="p-0">
                        <tr v-for="reminder in reminders">
                            <td><a href="javascript:;" v-on:click="deleteReminder(reminder.id)"><i
                                class="icon-trash-2"></i></a></td>
                            <td class="">
                                <a href="javascript:;" v-if="reminder.status == 1"
                                   v-on:click="pauseReminder(reminder.id)"><i class="fa fa-pause"></i></a>
                                <a href="javascript:;" v-if="reminder.status == 2"
                                   v-on:click="playReminder(reminder.id)"><i class="fa fa-play"></i></a>
                            </td>
                            <td class="" nowrap>
                                {{ reminder.title }}
                            </td>
                            <td class="" nowrap>
                                {{ (reminder.type == 1) ? translations.label.weekly : translations.label.diary }}
                            </td>
                            <td class="" nowrap>
                                {{ reminder.fecini }}
                            </td>
                            <td class="" nowrap>
                                {{ reminder.fecfin }}
                            </td>
                            <td class="" nowrap>
                                <span v-for="user in JSON.parse(reminder.users)" class="badge badge-warning">
                                {{ user }}
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!loadingModal && all_reminders == 0">
                    <div class="alert alert-warning mt-3 mb-3">{{ translations.messages.no_reminders_available }}.</div>
                </div>
            </div>
        </b-modal>

        <a :href="href_" ref="download_hotel" target="_self"></a>

    </div>
</template>
<style>
    .navbar .aurora-main .dropdown-menu .nav-primary > .nav-item {
        margin-bottom: 15px;
    }

    .back-new {
        background: #E2FDFF !important;
    }

    .bootstrap-datetimepicker-widget.dropdown-menu {
    }

    .force-right {
        right: 0;
        position: absolute !important;
    }
</style>
<script>
    import Cookies from 'js-cookie';
    import {cookiesAuth} from '../mixins/cookiesAuth'
    // Using font-awesome 5 icons
    $.extend(true, $.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    })
    export default {
        mixins: [cookiesAuth],
        data: () => {
            return {
                user: '',
                username: '',
                photo: '',
                access_token: localStorage.getItem('access_token'),
                user_type_id: localStorage.getItem('user_type_id'),
                view_permissions: JSON.parse(localStorage.getItem('view_permissions')),
                user_id: localStorage.getItem('user_id'),
                rol: localStorage.getItem('rol'),
                csrf_token: '',
                client_id: '',
                client_code: '',
                clients: [],
                permissions: [],
                clientSelect: [],
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
                lang: 'en',
                languages: [],
                popup_quotes: [],
                popup_quotes_totals: {
                    no_viewed: 0,
                    total: 0
                },
                route_name: '',
                cart: {
                    cart_content: [],
                    hotels: [],
                    services: [],
                    total_cart: 0.00,
                    quantity_items: 0
                },
                notifications: {
                    all: 0,
                    news: 0,
                    items: [],
                    time: ''
                },
                titleReminder: '',
                messageReminder: '',
                feciniReminder: '',
                fecfinReminder: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                },
                optionsT: {
                    format: 'h:mm:ss',
                    useCurrent: true,
                    showClear: true,
                    showClose: true,
                },
                typeReminder: '1',
                optionsS: [
                    { text: 'Semanal', value: 1 },
                    { text: 'Diario', value: 2 },
                ],
                priorities: [
                    { text: 'Baja', value: 'B' },
                    { text: 'Media', value: 'M' },
                    { text: 'Alta', value: 'A' },
                ],
                priorityReminder: '',
                users: [],
                usersReminder: [],
                hourReminder: '',
                reminders: [],
                all_reminders: 0,
                modal_reminders: false,
                loadingModal: false,
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                code: localStorage.getItem('code'),
                user_invited: false,
                blockPage: false,
                limit_clients: 40,
                client_allow_direct_passenger_create: false,
                disable_reservation: false,
                href_ : "",
                disabled_search_clients : false,
                client_name : '',
                TokenKey: window.tokenKey,
                UserKey: window.userKey,
                Domain: { domain: window.domain },
                client: {},
            }
        },
        created () {
            this.csrf_token = document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')

            if(!this.verifyToken()) {
                return
            }

            this.getLanguages()

            this.lang = document.querySelector('meta[name=\'lang\']').getAttribute('content')
            localStorage.setItem('lang', this.lang)

            this.route_name = document.querySelector('meta[name=\'route_name\']').getAttribute('content')
            console.log(this.route_name)
            if(this.route_name === 'reservations.personal_data' || this.route_name === 'cart_details'){
                this.disabled_search_clients = true
            }
            this.user = document.querySelector('meta[name=\'code\']').getAttribute('content')
            let username = document.querySelector('meta[name=\'username\']').getAttribute('content')
            this.username = (username == '') ? this.user : username
            if (localStorage.getItem('client_id')) {
                if (localStorage.getItem('client_id') != '' && localStorage.getItem('client_id') != null) {
                    this.client_id = localStorage.getItem('client_id')
                }
            }
            if (localStorage.getItem('client_code')) {
                if (localStorage.getItem('client_code') != '' && localStorage.getItem('client_code') != null) {
                    this.client_code = localStorage.getItem('client_code')
                }
            }
            if (localStorage.getItem('client_name')) {
                if (localStorage.getItem('client_name') != '' && localStorage.getItem('client_code') != null) {
                    this.client_name = localStorage.getItem('client_name')
                }
            }
            if (localStorage.getItem('permissions')) {
                if (localStorage.getItem('permissions') !== '' && localStorage.getItem('permissions') != null) {
                    this.permissions = JSON.parse(localStorage.getItem('permissions'))
                }
            }

            if (this.route_name != 'hotels') {
                localStorage.setItem('reservation', false)
            }
            this.$root.$on('updateMenu', async () => {
                await this.getCartContent();
                this.searchPopupQuotes();
            })
            this.$root.$on('executeMenuClearCart', async (redirect) => {
                console.log("valor de redirect", redirect);
                await this.clearCart(redirect);
                console.log("termino de ejecutar clearCart");
            })
            this.code = localStorage.getItem('code')
        },
        mounted () {
            if(!this.verifyToken()) {
                this.logout()
            }
            this.loadPhoto()
            this.loadUsers()
            this.loadReminders()
            this.setTranslations()

            let user_email_ = localStorage.getItem('user_email')
            let email_64 = new Buffer(user_email_)
            email_64 = email_64.toString('base64')

            this.user_invited = (this.code === 'guest')

            if (this.canUsePushNotifications()) {
                this.registerPush()
                this.verifySetPush()
            } else {
                console.log('Push notifications no disponibles en este navegador o contexto.')
            }

            if (localStorage.getItem('client_id') != '' && localStorage.getItem('client_id') != null) {
                //document.getElementById("cliente_select").remove(0)
            }
            if (this.route_name !== 'reservations.personal_data') {
                this.getCartContent();
            }

            if (this.user_type_id == 3) {
                this.getClientsByExecutive()
                if (localStorage.getItem('reservation') == 'false') {
                    this.$root.$emit('updatedestiniesandclass')
                }
            }

            if (this.user_type_id == 4) {
                if (localStorage.getItem('reservation') == 'false') {
                    this.$root.$emit('updatedestiniesandclass')
                }
            }

            this.searchNotifications('')

            let _function = localStorage.getItem('_function')
            if (_function != '') {
                eval(_function)
            }

            this.searchPopupQuotes()

            if(document.head.querySelector("[name=route_name]")){

                let page = document.head.querySelector("[name=route_name]").content;

                if(!['services','hotels','cart_details','reservations.personal_data'].includes(page)){
                    localStorage.setItem('search_params', '')
                }

            }else{
                localStorage.setItem('search_params', '')
            }

        },
        methods: {
            canUsePushNotifications: function () {
                return typeof window !== 'undefined' &&
                    window.isSecureContext &&
                    typeof firebase !== 'undefined' &&
                    'serviceWorker' in navigator &&
                    'Notification' in window
            },
            getMessagingInstance: function () {
                if (!this.canUsePushNotifications()) {
                    return null
                }

                try {
                    return firebase.messaging()
                } catch (error) {
                    console.error('No se pudo inicializar Firebase Messaging.', error)
                    return null
                }
            },
            goToA3(_link) {
                return window.a3BaseUrl + _link
            },
            logout() {
                this.removeCookies()
                document.getElementById('logout-form').submit();
            },
            onSearchClient (search, loading) {
                if (search.length >= 2) {
                    loading(true)
                    this.debounce(() => {
                        this.clients = []
                        axios.get(baseExternalURL + 'api/clients/selectBox/by/executive?queryCustom=' + search + '&limit=' + this.limit_clients).then((result) => {
                            loading(false)
                            if (result.data.success === true) {
                                this.clients = result.data.data
                            }
                        }).catch(() => {
                            loading(false)
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Home',
                                text: this.$t('global.error.messages.information_error'),
                            })
                        })
                    }, 300)
                }
            },
            debounce (method, timer) {
                if (this.$_debounceTimer !== null) {
                    clearTimeout(this.$_debounceTimer)
                }
                this.$_debounceTimer = setTimeout(() => {
                    method()
                }, timer)
            },
            roundLito: function (num) {
                num = parseFloat(num)
                num = (num).toFixed(2)

                if (num != null) {
                    var res = String(num).split('.')
                    var nEntero = parseInt(res[0])
                    var nDecimal = 0
                    if (res.length > 1)
                        nDecimal = parseInt(res[1])

                    var newDecimal
                    if (nDecimal < 25) {
                        newDecimal = 0
                    } else if (nDecimal >= 25 && nDecimal < 75) {
                        newDecimal = 5
                    } else if (nDecimal >= 75) {
                        nEntero = nEntero + 1
                        newDecimal = 0
                    }

                    return parseFloat(String(nEntero) + '.' + String(newDecimal))
                }
            },
            getRouteExcelServiceYear: function (year) {
                this.blockPage = true
                axios.get(
                    baseExternalURL + 'api/services/' + year + '/export?lang=' + localStorage.getItem('lang') + '&client_id=' + localStorage.getItem('client_id') + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                ).then((result) => {
                    console.log(result)
                    this.blockPage = false
                    if (result.data.success) {
                        this.$toast.success(this.translations.label.send_rate_email, {
                            position: 'top-right'
                        })
                    } else if (result.data.success == false && result.data.status_download == true) {
                        this.$toast.error(this.translations.label.i_already_made_request, {
                            position: 'top-right'
                        })
                    } else {
                        this.$toast.error(this.translations.messages.an_error_occurred, {
                            position: 'top-right'
                        })
                    }
                }).catch((e) => {
                    console.log(e)
                    this.blockPage = false
                    this.$toast.error(this.translations.messages.an_error_occurred, {
                        // override the global option
                        position: 'top-right'
                    })
                })
            },
            getRouteExcelHotel: function (year) {
                this.blockPage = true
                axios.get(
                    baseExternalURL + 'api/hotels/generate_array/' + year + '?lang=' + localStorage.getItem('lang') + '&client_id=' + localStorage.getItem('client_id') + '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' + localStorage.getItem('user_type_id')
                )
                    .then((result) => {
                        console.log(result)

                        this.href_ = baseExternalURL + 'hotels/export/' + year + '?lang=' +
                            localStorage.getItem('lang') + '&client_id=' + localStorage.getItem('client_id') +
                            '&user_id=' + localStorage.getItem('user_id') + '&user_type_id=' +
                            localStorage.getItem('user_type_id')

                        this.$nextTick(() => {
                            this.$refs.download_hotel.click()
                        })
                        this.$toast.success("Downloading ...", {
                            // override the global option
                            position: 'top-right'
                        })
                        let me = this
                        setTimeout(function(){
                            me.blockPage = false
                        }, 18000)
                    }).catch((e) => {
                    console.log(e)
                })
            },
            isWhiteMarch: function () {
                const permitted = ['ADMIN', 'CLO', 'SNB', 'JCH'];
                const user = localStorage.getItem('code').toUpperCase();
                const response = permitted.indexOf(user) > -1;

                return response;
            },
            hasPermission: function (permission, action) {
                let index = 0
                let flag_permission = false
                let enable = false
                for (let i = 0; i < this.permissions.length; i++) {
                    if (this.permissions[i].subject === permission) {
                        flag_permission = true
                        index = i
                    }
                }
                if (flag_permission) {
                    for (let a = 0; a < this.permissions[index].actions.length; a++) {
                        if (this.permissions[index].actions[a] === action) {
                            enable = true
                        }
                    }
                }
                return enable
            },
            setTranslations () {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                    this.translations = data.data
                })
            },
            loadPhoto: function () {
                axios.get(
                    baseURL + 'account/find_photo'
                )
                    .then((result) => {
                        this.photo = result.data.photo
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            updateShowInPopup () {
                axios.put(window.a3BaseQuoteServerURL + 'api/quotes/showInPopup/0')
                    .then((result) => {
                        // console.log(result)
                        this.popup_quotes_totals.no_viewed = 0
                    }).catch((e) => {
                    console.log(e)
                })
            },
            searchPopupQuotes () {
                axios.get(window.a3BaseQuoteServerURL + 'api/quotes?lang=' + localStorage.getItem('lang') +
                    '&page=1&limit=5&filterBy=activated')
                    .then((result) => {
                        this.popup_quotes = result.data.data
                        this.popup_quotes_totals.total = result.data.totals.total
                    }).catch((e) => {
                    console.log(e)
                })
            },
            getLanguages: function () {
                axios.get(baseExternalURL + 'api/languages')
                    .then((result) => {
                        this.languages = result.data.data
                    }).catch((e) => {
                    console.log(e)
                })
            },
            openSupportDesk() {

            axios.post(baseExternalURL + 'api/sso/generate-url')
                .then((response) => {
                    if (response.data?.url) {
                        window.open(response.data.url, '_blank')
                    } else {
                        console.error('No se pudo generar la URL de acceso.')
                    }
                    })
                    .catch((error) => {
                    console.error('Error al generar URL del SSO:', error)
                })
            },
            loadUsers: function () {
                axios.post(
                    baseURL + 'search_users'
                )
                    .then((result) => {
                        this.users = result.data.users
                    }).catch((e) => {
                    console.log(e)
                })
            },
            loadReminders: function () {
                axios.post(
                    baseURL + 'count_reminders'
                )
                    .then((result) => {
                        this.all_reminders = result.data.all_reminders
                    }).catch((e) => {
                    console.log(e)
                })
            },
            registerPush: function () {
                if (!this.canUsePushNotifications()) {
                    return
                }

                var config = {
                    apiKey: 'AIzaSyByFvuZUxj1dOajExlm7BAnJdqksXsqri4',
                    authDomain: 'firebase-limatours.firebaseapp.com',
                    databaseURL: 'https://firebase-limatours.firebaseio.com',
                    projectId: 'firebase-limatours',
                    storageBucket: 'firebase-limatours.appspot.com',
                    messagingSenderId: '541786854376'
                }

                if (!firebase.apps.length) {

                    firebase.initializeApp(config)
                    // Supported!
                    // REGISTRAR EL WORKER
                    navigator.serviceWorker.register(window.origin + '/sw.js').then(registro => {
                        firebase.messaging().useServiceWorker(registro)

                        // console.log("Registro Correcto: " + registro)
                    })
                        .catch(error => {
                            console.log(error)
                        })
                    // REGISTRAR EL WORKER

                    const messaging = this.getMessagingInstance()
                    if (!messaging) {
                        return
                    }

                    // Registrar credenciales web
                    messaging.usePublicVapidKey(
                        'BPqRLq6SsAsJTUUNwfyyd94RvLBbIf6vZp078YDiLri4sF9OSlh7bLvqw1RnusZLnxJLH30-pk2BdLPTZCDjpMc'
                    )

                    //  Recibir las notificaciones cuando el usuario esta foreground
                    messaging.onMessage(playload => {
                        console.log(playload)
                        this.searchNotifications()

                        this.$toast.success(playload.notification.title, {
                            // override the global option
                            position: 'top-right'
                        })
                    })
                }
            },
            verifySetPush: function () {
                this.loading = true
                const messaging = this.getMessagingInstance()
                if (!messaging) {
                    this.loading = false
                    return
                }
                messaging.getToken()
                    .then(token => {
                        if (token != null && token != '') {
                            const db = firebase.firestore()
                            db.settings({ timestampsInSnapshots: true })
                            db.collection('tokens').doc(token).set({
                                token: token
                            }).catch(error => {
                                console.error(error)
                            })

                            this.area1 = token
                        } else {
                            console.log('Usuario no registrado.. Solicitando permiso..')
                            this.getSubscription()
                        }
                    })
                    .catch(error => {
                        this.loading = false
                        console.error('No se pudo obtener el token de Firebase.', error)
                    })
            },
            sendMessageTest: function () {

                console.log(this.para)

                let myData = {
                    'to': this.para,
                    'notification': {
                        'title': 'Prueba',
                        'body': 'Prueba de contenido..',
                        // "image" : window.origin + '/images/viaje.jpg',
                        // "icon" : window.origin + '/images/favicon.png',
                        'click_action': 'http://google.com'
                    }
                }

                /*
                $.ajax({
                    url: 'https://fcm.googleapis.com/fcm/send',
                    type: 'POST',
                    data: JSON.stringify(myData),
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'key=' + 'AAAAfiUDU-g:APA91bGI-fdhLGFb9PrvB0OSVUOV2RzmFoKIPSL10df9U7u5J-K8t4hk-kPq1ZQRlGOENFBoGOHfRoELTVR--h1j4FD_O1eejbE5-TP7S9SM2TNtSbJdebrA-qgxxqFi9qfkQoT8pUPH'
                    },
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
                */
            },
            getSubscription: function () {
                const messaging = this.getMessagingInstance()
                if (!messaging) {
                    this.loading = false
                    return
                }

                if (Notification.permission === 'denied') {
                    this.loading = false
                    console.warn('El usuario bloqueó las notificaciones.')
                    return
                }

                messaging.requestPermission()
                    .then(() => {
                        return messaging.getToken()
                    })
                    .then(token => {
                        console.log('token', token)

                        if (token != '') {
                            this.area1 = token

                            axios.post(baseURL + 'register_push_notification', {
                                token: token
                            })
                                .then((result) => {
                                    if (result.data.success) {
                                        this.$toast.success(this.translations.label.token_registered, {
                                            // override the global option
                                            position: 'top-right'
                                        })
                                    }
                                })
                                .catch((e) => {
                                    console.log(e)
                                    this.$toast.error(this.translations.messages.an_error_occurred, {
                                        // override the global option
                                        position: 'top-right'
                                    })
                                })

                            const db = firebase.firestore()
                            db.settings({ timestampsInSnapshots: true })
                            db.collection('tokens').doc(token).set({
                                token: token
                            })
                                .catch(error => {
                                    this.stateSubscription = false
                                    console.error(error)
                                })
                        } else {
                            console.log('Error al brindar el permiso para firebase')
                        }
                    })
                    .catch(error => {
                        this.loading = false
                        console.error('No se pudo completar la suscripción a Firebase.', error)
                    })
            },
            /*
            listTokens: function(){
                API({
                    method: 'GET',
                    url: 'user/notification/token'
                })
                .then((result) => {

                }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('account.error.messages.name'),
                        text: this.$t('account.error.messages.connection_error')
                    })
                })
            },
            */
            // PUSH NOTIFICATION..
            formatDate: function (starDate) {
                return moment(starDate).format('ddd D MMM')
            },
            eventoMenu: function () {
                if ($('.backdrop-banners').length > 0) {
                    $('.dropdown').on('show.bs.dropdown', function (event) {
                        $('.backdrop-banners').css('display', 'block').animate({
                            'opacity': 0
                        }, 20)
                    })
                    $('.dropdown').on('hide.bs.dropdown', function (event) {
                        $('.backdrop-banners').animate({
                            'opacity': 0
                        }, 10).fadeOut()
                    })
                }
            },
            setLang: function () {
                axios.get(baseURL + 'lang/' + this.lang)
                    .then((result) => {
                        window.location.reload()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            getClientsByExecutive: function () {
                axios.get('api/clients/selectBox/by/executive?limit=' + this.limit_clients)
                    .then((result) => {
                        if (result.data.success === true) {
                            this.clients = result.data.data
                            if (localStorage.getItem('client_id') != '' && localStorage.getItem('client_id') != null) {
                                this.clientSelect.push({
                                    client_code: localStorage.getItem('client_code'),
                                    code: localStorage.getItem('client_id'),
                                    label: localStorage.getItem('client_name'),
                                    allow_direct_passenger_create: localStorage.getItem('client_allow_direct_passenger_create')
                                })
                            }
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            getDestiniesByClientIdOnChange: function (value) {

                if (document.getElementById('_overlay') != null) {
                    document.getElementById('_overlay').style.display = 'none'
                }
                let do_refresh = 0
                if(this.client_id===''){
                    do_refresh = 1
                }
                this.client_id = value.code
                this.client_code = value.client_code
                this.client_name = value.label
                this.client_allow_direct_passenger_create = value.allow_direct_passenger_create
                if (localStorage.getItem('client_id') != '') {
                    if (this.cart.hotels.length > 0) {
                        var result = confirm(this.translations.messages.if_you_change_customers)
                        if (result == true) {
                            this.destroyCart()
                            localStorage.setItem('client_id', this.client_id)
                            localStorage.setItem('client_code', this.client_code)
                            localStorage.setItem('client_name', this.client_name)
                            localStorage.setItem('client_allow_direct_passenger_create', this.client_allow_direct_passenger_create)
                            Cookies.set(window.userClientId, this.client_id, {
                                domain: window.domain
                            });
                            this.$root.$emit('updatedestiniesandclass')
                            window.location.reload()
                        } else {
                            this.client_id = localStorage.getItem('client_id')
                            if (this.route_name == 'hotels') {
                                this.$root.$emit('updatedestiniesandclass')
                            }
                        }
                    } else {
                        localStorage.setItem('client_id', this.client_id)
                        localStorage.setItem('client_code', this.client_code)
                        localStorage.setItem('client_name', this.client_name)
                        localStorage.setItem('client_allow_direct_passenger_create', this.client_allow_direct_passenger_create)
                        Cookies.set(window.userClientId, this.client_id, {
                            domain: window.domain
                        });
                        this.$root.$emit('updatedestiniesandclass')
                    }
                } else {
                    localStorage.setItem('client_id', this.client_id)
                    localStorage.setItem('client_code', this.client_code)
                    localStorage.setItem('client_name', this.client_name)
                    localStorage.setItem('client_allow_direct_passenger_create', this.client_allow_direct_passenger_create)
                    Cookies.set(window.userClientId, this.client_id, {
                        domain: window.domain
                    });
                }

                this.$root.$emit('changeMarkup')

                if(this.client_id===''){
                    do_refresh = 0
                }
                if(do_refresh){
                    location.reload()
                }

            },
            getCartContent: async function () {
                try {
                    // console.log("Actualización del carrito de la cabecera..")
                    const result = await axios.get(baseURL + 'cart');

                    if (result.data.success === true) {
                        // + Prices of multiservices
                        result.data.cart.total_cart = parseFloat(result.data.cart.total_cart.replace(/[^\d\.\-eE+]/g, ''));

                        result.data.cart.services.forEach((s) => {
                            s.service.components.forEach((c) => {
                                s.total_service += c.total_amount;
                                result.data.cart.total_cart += c.total_amount;
                            });
                            s.total_service = this.roundLito(s.total_service);
                        });
                        // + Prices of multiservices

                        this.cart = result.data.cart;
                        this.cart_quantity_items = result.data.cart.quantity_items;
                    }
                    // console.log('this.route_name ' + this.route_name);
                    // if (this.route_name == 'reservations.personal_data') {
                    //     this.getDashboardData()
                    // }}
                } catch (error) {
                    console.error('Error getting cart content:', error);
                    this.$toast.error('Error al cargar el carrito. Por favor, inténtelo de nuevo.', {
                        position: 'top-right'
                    });
                }
                this.eventoMenu();
            },
            deleteCart: async function (index_item) {
                try {
                    const result = await axios.delete(baseURL + 'cart/' + index_item);

                    if (result.data.success === true) {
                        await this.getCartContent();
                    }
                } catch (error) {
                    console.error('Error deleting cart item:', error);
                }
            },
            destroyCart: async function () {
                try {
                    const result = await axios.delete(baseURL + 'cart/content/delete');

                    if (result.data.success) {
                        await this.getCartContent();
                    }
                    console.log(result.data.data);
                } catch (error) {
                    console.error('Error destroying cart:', error);
                }
            },
            cancelRoomsCart: async function (room, hotel) {
                try {
                    const result = await axios.post(baseURL + 'cart/cancel/rates', {
                        cart_items_id: room.cart_items_id
                    });

                    if (result.data.success) {
                        if (this.route_name == 'hotels') {
                            this.$root.$emit('cancelcartmodal', { room_id: room.room_id, hotel: hotel });
                        }
                        await this.getCartContent();
                    }
                } catch (error) {
                    console.error('Error canceling room cart:', error);
                }
            },
            cancelServiceCart: async function (service) {
                try {
                    console.log(service);
                    const result = await axios.delete(baseURL + 'cart/' + service.cart_items_id);

                    if (result.data.success) {
                        // if (this.route_name == 'services') {
                        //   this.$root.$emit('cancelcartmodal', { room_id: room.room_id, hotel: hotel })
                        // }
                        await this.getCartContent();
                        this.$root.$emit('getCartContentMenu');
                    }
                } catch (error) {
                    console.error('Error canceling service cart:', error);
                }
            },
            searchNotifications: function () {

                if (this.$root.$data._module != undefined) {
                    axios.post(
                        baseURL + 'notifications', {
                            time: '',
                            module: this.$root.$data._module
                        }
                    )
                        .then((result) => {
                            console.log(result.data.notifications)
                            this.notifications = result.data.notifications
                        })
                        .catch((e) => {
                            console.log(e)
                            if (e.message == 'Unauthenticated.') {
                                window.location.reload()
                            }
                        })
                }
            },
            _showNotification: function (_module, _function, id, data) { // Cargando la notificación general
                this.updateNotification(id)

                try {
                    eval('this.$root.' + _function + '(' + id + ', ' + JSON.stringify(data) + ')')
                } catch (error) {
                    localStorage.setItem('_function', 'this.$root.' + _function + '(' + id + ', ' + JSON.stringify(data) + ')')
                    window.location.href = _module
                }
            },
            updateNotification: function (_id) {
                axios.post(
                    baseURL + 'update_notification', {
                        id: _id
                    }
                )
                    .then((result) => {
                        console.log(result)
                    })
                    .catch((e) => {
                        console.log(e)
                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            filterUsers: function (search, loading) {
                axios.post(
                    baseURL + 'search_users', {
                        filter: search
                    }
                )
                    .then((result) => {
                        this.users = result.data.users
                    }).catch((e) => {
                    console.log(e)
                })
            },
            saveReminder: function () {

                if (this.titleReminder == '') {
                    this.$toast.error(this.translations.messages.enter_a_title_to_generate_the_reminder, {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if (this.messageReminder == '') {
                    this.$toast.error(this.translations.message.enter_your_message, {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if (this.priorityReminder == '') {
                    this.$toast.error(this.translations.messages.enter_a_priority, {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if (this.hourReminder == '') {
                    this.$toast.error(this.translations.messages.enter_one_hour, {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                axios.post(
                    baseURL + 'save_reminder', {
                        title: this.titleReminder,
                        fecini: this.feciniReminder,
                        fecfin: this.fecfinReminder,
                        users: this.usersReminder,
                        message: this.messageReminder,
                        type: this.typeReminder,
                        priority: this.priorityReminder,
                        hour: this.hourReminder
                    }
                )
                    .then((result) => {
                        this.all_reminders = result.data.all_reminders
                        if (result.data.type == 'success') {
                            this.titleReminder = ''
                            this.feciniReminder = ''
                            this.fecfinReminder = ''
                            this.usersReminder = []
                            this.messageReminder = ''
                            this.typeReminder = 1
                            this.priorityReminder = ''
                            this.hourReminder = ''

                            this.$toast.success(this.translations.messages.reminder_generated_successfully, {
                                // override the global option
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error(result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            showReminders: function () {
                this.modal_reminders = true
                this.loadingModal = true

                axios.post(
                    baseURL + 'search_reminders', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loadingModal = false
                        this.reminders = result.data.reminders
                        this.all_reminders = result.data.all_reminders
                    }).catch((e) => {
                    console.log(e)
                })
            },
            pauseReminder: function (reminderID) {
                this.loadingModal = true

                axios.post(
                    baseURL + 'pause_reminder', {
                        lang: this.lang,
                        reminder: reminderID
                    }
                )
                    .then((result) => {
                        this.showReminders()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            playReminder: function (reminderID) {
                this.loadingModal = true

                axios.post(
                    baseURL + 'play_reminder', {
                        lang: this.lang,
                        reminder: reminderID
                    }
                )
                    .then((result) => {
                        this.showReminders()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            deleteReminder: function (reminderID) {
                this.loadingModal = true

                axios.post(
                    baseURL + 'delete_reminder', {
                        lang: this.lang,
                        reminder: reminderID
                    }
                )
                    .then((result) => {
                        this.showReminders()
                    }).catch((e) => {
                    console.log(e)
                })
            },
            goCartDetails(){
                if(document.head.querySelector("[name=route_name]")){
                    let page = document.head.querySelector("[name=route_name]").content;
                    if(['services','hotels'].includes(page)){
                        localStorage.setItem('page_return', document.head.querySelector("[name=route_name]").content)

                    }
                    document.location.href='/cart_details/view'
                }

            },
            clearCart: async function(redirect = true) {
                try {
                    console.log("valor de clearCart y redirect", redirect);
                    const result = await axios.post(baseURL + 'cart/cancel/clear');

                    if (result.data.success) {
                        localStorage.setItem('search_params', '');

                        if (redirect) {
                            let page = document.head.querySelector("[name=route_name]").content;
                            if (['cart_details','reservations.personal_data'].includes(page)) {
                                document.location.href = "/hotels";
                            } else {
                                await this.getCartContent();
                            }
                        } else {
                            await this.getCartContent();
                        }
                    }
                } catch (error) {
                    console.error('Error clearing cart:', error);
                    this.$toast.error('Error al limpiar el carrito. Por favor, inténtelo de nuevo.', {
                        position: 'top-right'
                    });
                }
            },

        },
        filters: {
            reformatDate: function (_date) {
                if (_date == undefined) {
                    return
                }
                _date = moment(_date).format('ddd D MMM YYYY')
                return _date
            },
        }
    }
</script>
