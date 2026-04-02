<template>
    <div class="container serie-general series-body ">
      <div class="page-hoja-master edit-series container-series">

        <div class="contenedor">
          <h1 class="titulo">Grupos & Series</h1>
        </div>
        <div class="contenedor">
          <h2 class="subtitulo">{{ serie.name }}<span>{{ translations.label.by }} {{ serie.user.name }}</span></h2>
        </div>
        <div class="contenedor">
          <div class="tour">
            <div class="tour-info">
              <div class="tour-info-codigo">S-{{ serie_id_decode }}</div>
              <div class="tour-info-descripcion salida">{{ translations.label.departure }}<span>{{ serie.date_start | reformatDate }}</span></div>
              <div class="tour-info-modal">

                <button :disabled="loading" v-if="!(user_invited)" class="dropdown-toggle" data-toggle="dropdown" style="border: none;">
                  <i class="icon-message-square"></i><span>{{ translations.label.comments }}</span>
                </button>
                <div class="dropdown-menu dropdown-nota-mensaje" style="margin-top: 10px">
                  <div class="dropdown-content">
                    <span v-if="serie.comment !== '' && serie.comment !== null">{{ serie.comment }}</span>
                    <span v-else>(Sin comentarios)</span>
                  </div>
                </div>

              </div>
              <div class="tour-info-modal" data-toggle="modal" data-target="#modal-info">
                <button :disabled="loading" v-if="!(user_invited)" :class="'permission_'+type_permission"
                        @click="toggleModal( 0, 'edit', { translations: translations, serie_selected : serie } )"
                        data-toggle="modal" data-target="#modal-info">
                  <i class="icon-edit"></i><span>{{ translations.label.edit }} {{ translations.label.info }}</span>
                </button>
              </div>
            </div>
            <button id="modal_force_2" data-toggle="modal" data-target="#modal-import-master-sheet" @click="forceModal()"></button>
            <button id="modal_force_3" data-toggle="modal" data-target="#modal-import-serie" @click="forceModal()"></button>
            <button id="modal_force_4" data-toggle="modal" data-target="#modal-simple-departures" @click="forceModal()"></button>
            <button id="modal_force_5" data-toggle="modal" data-target="#modal-advance-departures" @click="forceModal()"></button>
            <div class="tour-opciones">
              <button class="descargar">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" title="Recordatorio" :disabled="loading">
                  <span class="icon-bell"></span>
                  <span class="numero">0</span>
                </div>
              </button>
              <button class="descargar" v-if="!(user_invited)"
                      @click="toggleModal( 0, 'note', { translations: translations, serie_id : serie.id, type_permission : type_permission } )" data-toggle="modal" data-target="#modal-notas">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.notes" :disabled="loading">
                  <span class="icono icon-file-text"></span>
                  <span class="numero">{{ total_notes }}</span>
                </div>
              </button>
              <button class="descargar mensajes mesajes-nuevo" v-if="!(user_invited)"
                      @click="toggleModal( 0, 'message', { translations: translations, serie_id : serie.id, serie_created_at: serie.created_at,
                      master_sheet_id: serie.master_sheet_id, see_previous_messages : serie.see_previous_messages, master_sheet_id : serie.master_sheet_id } )"
                      data-toggle="modal" data-target="#modal-mensajes" :disabled="loading">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.messages">
                  <span class="icono icon-mail"></span>
                  <div class="numero">
                    {{ serie.messages_count }}
                  </div>
                </div>
              </button>
              <button class="descargar permission_0_1" v-if="!(user_invited)" :class="'permission_'+type_permission"
                      @click="toggleModal( 0, 'share', { translations: translations, serie_id : serie.id } )"
                      data-toggle="modal" data-target="#modal-compartir" :disabled="loading">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" :title="translations.label.share">
                  <span class="icono icon-share-2"></span><span class="numero">{{ serie.users_count }}</span>
                </div>
              </button>
              <button class="descargar ml-4" v-if="!(user_invited)"
                      data-toggle="tooltip" data-placement="top" title="Todas mis Series" :disabled="loading">
                <a class="icono icon-archive" href="/series"></a>
              </button>
              <button class="descargar">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" title="Historial" :disabled="loading">
                  <span class="icon-rotate-ccw"></span>
                  <!--                  <span class="icon-icon-history"></span> -->
                </div>
              </button>
              <div class="descargar dropdown dropdown-importar" data-toggle="tooltip" data-placement="top"
                   :title="translations.label.import" v-if="!(user_invited)">
                <button class="dropdown-toggle dropdown-nota-button icono icon-download" :class="'permission_'+type_permission"
                        data-toggle="dropdown" style="border: none;"></button>
                <div class="dropdown-menu dropdown-nota-mensaje">
                  <div class="dropdown-content" :class="'permission_'+type_permission">
                    <a href="javascript:;"
                       @click="toggleModal( 2, 'import-master-sheet', { translations: translations, serie_id : serie.id } )">
                      {{ translations.label.import }} {{ translations.label.title }}
                    </a>
                    <a href="javascript:;"
                       @click="toggleModal( 3, 'import-serie', { translations: translations, serie_id : serie.id } )">
                      {{ translations.label.import }} Grupos & Series
                    </a>
                  </div>
                </div>
              </div>

              <button class="descargar" v-if="!(user_invited) && type_permission===0"
                      @click="toggleModal( 0, 'remover', { translations: translations, serie_id : serie.id, action_after : 'reload_list' } )"
                      data-toggle="modal" data-target="#modal-borrar-itinerario" :disabled="loading">
                <span class="icono icon-trash-all" data-toggle="tooltip" data-placement="top" :title="translations.label.delete_itinerary"></span>
              </button>
              <button class="descargar">
                <div class="descargar-tooltip" data-toggle="tooltip" data-placement="top" title="Crear copia" :disabled="loading">
                  <span class="icon-save"></span>
<!--                  <span class="icon-icon-save-copy"></span>-->
                </div>
              </button>
              <button class="button-red">Guardar</button>

            </div>
          </div>
        </div>

        <div class="container-series__content">

          <!-- DETAIL  -->
          <div class="content-detail">
            <div class="content-detail__left">

              <div class="left-content filtro-group">
                <div class="left-content__dropdown--container dropdown-alternative">
                  <button class="dropdown-trigger interaction" @click="show_drop_departures=true"
                          data-toggle="dropdown" style="border: none;" title="Salidas">
                    <span class="icon-calendar-confirm"></span>
                    <span class="left-content__number">{{ total_departures }}</span>
                  </button>
                  <div class="dropdown" v-show="show_drop_departures">
                    <div class="dropdown-container__content dropdown-alternative__content dropdown-menu" ref="dropdown">
                      <div class="content-departure dropdown-content">
                        <a class="content-departure_close dropdown-alternative__content__close" href="javascript:;"
                           id="dropdown-alternative__content__close1" @click="show_drop_departures=false">x</a>
                         <div class="content-departure__top">
                          <div class="container">

                            <span class="top-date">Fecha de salida</span>
                            <div class="top-months" >

                                    <ul class="months-content">
                                      <li class="content-month" v-for="departure in serie_departures">
                                        <p class="content-month__txt">{{ departure.month_name | capitalize }} <span class="txt-gray">/ {{ departure.year }}</span></p>
                                        <div class="content-days">

                                          <div class="content-days__txt" :class="{'content-days__txt--selection': day.choose}"
                                               v-for="day in departure.dates" @click="choose_departure_for_delete(day)">
                                            <div class="">
                                              <p>{{ day.label_1 }} <span class="txt-bold">{{ day.label_2 }}</span></p>
                                            </div>

                                          </div>

                                        </div>
                                      </li>
                                      <!-- ADD CLASS -->
                                      <div class="alert alert-warning" v-show="show_delete_departures" style="margin: 15px 5px 15px 0; line-height: 20px;">
                                        <span class="content-message__alert">Selecciona los meses o días que desees eliminar.</span>
                                        <span class="content-message__delete" v-if="total_delete_departures>1">Se eliminarán {{ total_delete_departures }} salidas y todo lo asociado a ellas.</span>
                                      </div>

                                    </ul>

                        </div>
                          </div>


                        </div>
                        <div class="content-departure__bottom">

                          <div class="bottom-content">

                            <div class="content-actions" v-show="!show_delete_departures">
                              <div class="content-actions__left">
                                <p class="left-item specific-dates__modal-trigger"
                                   @click="toggleModal( 4, 'simple-departure', { translations: translations, serie_id : serie.id } )">
                                  <span class="icon-plus-circle "></span>
                                  <span class="left-item__txt">Fechas específicas</span>
                                </p>

                                <p class="left-item ranks-dates__modal-trigger"
                                   @click="toggleModal( 5, 'advance-departure', { translations: translations, serie_id : serie.id } )">
                                  <span class="icon-plus-circle"></span>
                                  <span class="left-item__txt">Rango de fechas</span>
                                </p>

                              </div>
                              <div class="content-actions__right trigger-delete" @click="show_delete_departures=true">
                                <p class="right-item">
                                  <span class="icon-trash-2"></span>
                                  <span class="right-item__txt">Eliminar fechas</span>
                                </p>
                              </div>
                            </div>

                            <div class="content-options" v-show="show_delete_departures" style="float: right; margin: 14px;">
                              <span class="content-options__cancel trigger-delete dropdown-alternative__content__close" @click="cancel_delete_departures()">Cancelar</span>
                              <span class="content-options__delete trigger-delete dropdown-alternative__content__close" v-if="total_delete_departures>0" @click="remove_departures()">Eliminar</span>
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="left-content filtro-group">
                <div class="left-content__dropdown--container dropdown-alternative">
                  <button class="dropdown-trigger interaction" @click="do_show_drop_categories()"
                       data-toggle="dropdown" style="border: none;"  title="Categorias">
                    <span class="icon-tag"></span>
                    <span class="left-content__number">{{ serie_categories.length }}</span>
                  </button>
                  <div class="dropdown " v-show="show_drop_categories">
                    <div class="dropdown-container__content dropdown-alternative__content content-category dropdown-menu" ref="dropdown">
                      <div class="dropdown-content content-category__content p-4" style="width: 250px; height: 200px;">
                        <div class="close-drop">
                          <a class="content-close dropdown-alternative__content__close" href="javascript:;" @click="show_drop_categories=false">x</a>
                        </div>
                        <div style="height: 200px;">
                          <div class="content-top drop-categories">
                            <span class="title-categories">Categorías</span>
                            <div class="content-top__form form-group">
                              <label class="form-check form-check-all">
                                <input class="form-check__input form-check-input" type="checkbox" @click="toggle_check_all_categories()" v-model="check_all_categories">
                                <span class="form-check__text form-check__text--title">Todas las categorias</span>
                              </label>
                              <label class="form-check form-check-second" v-for="category in categories">
                                <input class="form-check__input form-check-input" type="checkbox" v-model="category.check">
                                <span class="form-check__text">{{ category.translations[0].value }}</span>
                              </label>
                            </div>
                          </div>
                        </div>
                        <div class="bottom-drop">
                          <div class="content-bottom">
                            <div class="bottom-options">
                              <button type="button" :disabled="loading" class="bottom-options__cancel mr-3" @click="show_drop_categories=false">Cancelar</button>
                              <button type="button" :disabled="loading" class="bottom-options__apply" @click="save_categories()">Aplicar</button>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="left-content filtro-group">

                <div class="left-content__dropdown--container dropdown-alternative" id="dropdown-alternative3">
                  <button class="dropdown-trigger interaction" id="dropdown-trigger3" @click="show_drop_ranges=true"
                          data-toggle="dropdown" style="border: none;"  title="Rangos">
                    <span class="icon-maximize-2"></span>
                    <span class="left-content__number">{{ serie_ranges.length }}</span>
                  </button>

                  <div class="dropdown" v-show="show_drop_ranges">
                    <div class="dropdown-container__content dropdown-alternative__content dropdown-menu"  ref="dropdown">
                      <div class="content-ranks">
                        <div class="content-ranks__content">
                          <a class="content-close dropdown-alternative__content__close" @click="close_drop_ranges()">x</a>
                          <div class="content-top">
                            <div class="content-top__detail">
                              <p class="detail-title">Rangos</p>
                              <span class="detail-description">Define la relación Pax / Liberados</span>
                            </div>
                            <ul class="content-top__list">
                              <li class="list-item">
                                <div class="list-item__content list-item__content--title">
                                  <span class="content-pax content-pax--item">Pax Min.</span>
                                  <span class="content-escort">Escort</span>
                                  <span class="content-tc">TC</span>
                                  <span class="content-ghost"></span>
                                </div>
                              </li>
                              <li class="list-item" v-for="(range, r_key) in ranges">
                                <div class="list-item__content list-item__content--description">
                                  <input type="number" min="1" placeholder="5" v-model="range.min"  :disabled="range.id!==null"
                                         @keyup="verify_ranges_errors()" @change="verify_ranges_errors()"
                                         class="content-pax content-item--box content-pax--item-number" :class="{'el_error':range.error}">
                                  <input type="number" min="0" placeholder="1" v-model="range.free_scort" class="content-escort content-item--box ">
                                  <input type="number" min="0" placeholder="1" v-model="range.free_tc" class="content-tc content-item--box">
                                  <div class="content-actions">
                                    <button type="button" class="content-add" @click="add_range(range, r_key)">+</button>
                                    <button type="button" class="content-delete" :class="{'el_opacity':r_key===0}"
                                            @click="remove_range(range, r_key)" :disabled="r_key===0">-</button>
                                  </div>
                                </div>
                              </li>
                            </ul>
                          </div>
                          <div class="content-bottom">
                            <div class="bottom-options">
                              <button class="bottom-options__cancel" :disabled="loading" @click="close_drop_ranges()">Cancelar</button>
                              <button class="bottom-options__apply" :disabled="loading" @click="save_ranges()">Aplicar</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>

              </div>

              <div class="left-content filtro-group">
                <div class="left-content__dropdown--container dropdown-alternative" id="dropdown-alternative4">
                  <button class="dropdown-trigger interaction" id="dropdown-trigger4" @click="show_drop_rates=true"
                       data-toggle="dropdown" style="border: none;" title="Acompañantes">
                    <span class="icon-user-plus"></span>
                    <span class="left-content__number left-content__number--escort" v-if="serie_companions.length===0">
                      Acompañantes
                    </span>
                    <span class="left-content__number left-content__number--tc" v-else
                          v-for="serie_companion in serie_companions">{{ serie_companion.user_type.description }} {{ serie_companion.quantity }}
                        <span class="number-state">- {{ serie_companion.pay_mode.translations[0].value.substr(0, 3) }}</span>
                    </span>
                  </button>

                  <div class="dropdown" v-show="show_drop_rates">
                    <div class="dropdown-container__content dropdown-alternative__content dropdown-menu" ref="dropdown">
                    <div class="content-escort" >
                      <div class="content-escort__content">
                        <a class="content-close dropdown-alternative__content__close" @click="show_drop_rates=false">x</a>
                        <div class="content-top">
                          <div class="content-top__detail">
                            <p class="detail-title">Acompañantes</p>
                          </div>
                          <div class="content-top__box">
                            <div class="box-left">
                              <div class="box-left__form form-group">
                                <label class="form-check" v-for="companion in companions">
                                  <input class="form-check__input form-check-input" type="checkbox" v-model="companion.check">
                                  <span class="form-check__text form-check__text--title">{{ companion.description }}</span>
                                </label>
                              </div>
                            </div>
                            <div class="box-right">
                              <div class="box-right__escort" v-for="companion in companions">
                                <input class="values-txt" type="number" min="1" placeholder="2" v-model="companion.quantity" :disabled="!companion.check">
                                <select class="custom-select custom-select-drop" v-model="companion.pay_mode_id" :disabled="!companion.check">
                                  <option :value="pay_mode.id" v-for="pay_mode in pay_modes">{{ pay_mode.translations[0].value }}</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="content-bottom">
                          <div class="bottom-options">
                            <span class="bottom-options__cancel" @click="show_drop_rates=false">Cancelar</span>
                            <span class="bottom-options__apply" @click="save_companions()">Aplicar</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  </div>
                </div>
              </div>

              <div class="left-content">
                <div class="container-modal__aurora" data-toggle="modal" data-target="#modal_tarifas" data-backdrop="static"
                     @click="toggleModal( 0, 'rate-protections', { translations: translations, serie_id : serie.id } )">
                  <div class="dropdown-trigger interaction" data-toggle="tooltip" data-placement="top" title="Tarifas">
                    <span class="icon-shield1"></span>
                    <span class="left-content__number left-content__number--year" v-if="rate_protection===null">
                        Tarifas
                    </span>
                    <span class="left-content__number left-content__number--year" v-else>
                        {{ rate_protection.hotel }}-{{ rate_protection.service }}-{{ rate_protection.train }} %
                        <span class="number-state">{{ rate_protection.year }}</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="content-detail__right">
              <div class="right-content">
                <span class="right-content-alt__txt">Resúmen de la salida</span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Productos">
<!--                <span class="icon-clipboard"></span>-->
                <span class="icon-layers1"></span>
                <span class="right-content__txt">5</span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Pedidos">
<!--                <i class="right-content__icon icon-resource icon-serie-pedidos icon-resource&#45;&#45;yellow"></i>-->
                <span class="icon-inbox" style="color: #EA932D;"></span>
                <span class="right-content__txt">1</span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Confirmados">
<!--                <i class="right-content__icon icon-resource icon-serie-confirmado icon-resource&#45;&#45;cyan"></i>-->
                <span class="icon-thumbs-up" style="color: #04B5AA;"></span>
                <span class="right-content__txt">1</span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Observados">
<!--                <i class="right-content__icon icon-resource icon-serie-observado icon-resource&#45;&#45;red"></i>-->
                <span class="icon-thumbs-down" style="color: #CE3B4D;"></span>
                <span class="right-content__txt right-content__txt--alternative">1</span>
              </div>
              <div class="right-content icon-border" data-toggle="tooltip" data-placement="top" title="Files">
<!--                <span class="icon-icon-folder-added"></span>-->
                <div class="display: contents;">
                  <span class="icon-folder"></span>
                  <span class="icon-check icon-folder-add"></span>
                </div>

                <span class="right-content__txt">5</span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Cotizaciones">
                <span class="icon-inbox"></span>
              </div>
              <div class="right-content" data-toggle="tooltip" data-placement="top" title="Pedidos">
                <span class="icon-folder"></span>
              </div>
            </div>
          </div>
          <!-- TAB HEADER -->
          <div class="content-tab--header">
            <div class="content-tab--header__menu">
              <ul class="menu-client nav nav-tabs">
                <li class="menu-client__list" v-for="category in serie_categories">
                  <a class="list-item nav-link" :class="{'active':category.active}" @click="set_category_id_active(category.id)"
                     data-toggle="tab" href="javascript:;">
                    {{ category.type_class.translations[0].value }}
                  </a>
                </li>
              </ul>
              <ul class="menu-service nav">

                <div class="right-content right-content--select">
                  <select class="right-content__select custom-select_all" :disabled="loading" v-model="serie_departure_selected" @input="will_get_prices()">
                    <option :value="{}" selected disabled>Elija Fecha</option>
                    <optgroup class="select-group" v-for="departure in serie_departures" :label="departure.month_name.substr(0, 3).toUpperCase() + departure.year">
                      <option v-for="day in departure.dates" :value="day">{{ day.label_1 }} {{ day.label_2 }}/{{ departure.year }}</option>
                    </optgroup>
                  </select>
                </div>

                <div class="right-content right-content--select">
                  <select class="right-content__select custom-select_all" :disabled="loading" v-model="serie_range_selected" @input="will_get_prices()">
                      <option :value="{min:0}" selected disabled>Elija Rango</option>
                      <option :value="range" v-for="range in serie_ranges">Min {{ range.min }} pax</option>
                  </select>
                </div>

                <div class="dropdown dropdown-importar">
                  <button type="button" class="menu-service__button menu-service__button--extension nav-link dropdown-toggle"
                     data-toggle="dropdown" data-placement="bottom" :disabled="loading">
                    <span class="icon-plus-circle"></span> Productos
                  </button>

                  <div class="dropdown-menu dropdown-nota-mensaje">
                    <div class="dropdown-content">
                      <a href="javascript:;"
                         @click="toggleModal( 2, 'import-master-sheet', { translations: translations, action_after : 'go_edit' } )">
                        Servicio
                      </a>
                      <a href="javascript:;"
                         @click="toggleModal( 3, 'import-serie', { translations: translations, action_after : 'go_edit' } )">
                        Alojamiento
                      </a>
                      <a href="javascript:;"
                         @click="toggleModal( 1, 'edit', { translations: translations } )">
                        Programa
                      </a>
                    </div>
                  </div>

                </div>

              </ul>
            </div>
            <div class="content-tab--header__headline fixme">
              <div class="headline-check">
                <div data-toggle="tooltip" data-placement="top" title="Eliminar" style="font-size: 1.7rem;">
                  <span class="icon-check-square cursor-pointer" @click="show_delete_multiple=true" v-show="!show_delete_multiple"></span>
                  <span class="icon-trash-all cursor-pointer" @click="show_delete_multiple=false" v-show="show_delete_multiple"></span>
                </div>
              </div>
              <div class="headline-itinerary">
                <span class="headline-itinerary__txt">ITINERARIO</span>
              </div>
              <div class="headline-duration">
                <span class="headline-duration__txt">DURACIÓN</span>
              </div>
              <div class="headline-description">
                <span class="headline-description__txt">DESCRIPCIÓN</span>
              </div>
              <div class="headline-accommodation">
                <span class="headline-accommodation__txt">DETALLES</span>
              </div>
              <div class="headline-details">
                <span class="headline-details__txt">TIPO</span>
              </div>
              <div class="headline-released">
                <span class="headline-released__txt">LIBERADOS</span>
              </div>
              <div class="headline-released">
                <span class="headline-released__txt">TARIFA</span>
              </div>
              <div class="headline-state">
                <span class="headline-state__txt">ESTADO</span>
              </div>
            </div>
          </div>

          <!-- TAB BODY -->
          <div class="content-tab--body">
            <div class="content-tab--body__container">
              <div class="container-content container-content--modified tab-pane active">

                <div class="container-content__body container-content__body--draggable list-group">
                  <div class="body-group" v-for="serie_service in serie_services"
                       v-if="serie_service.type_code_service!=='component' && serie_service.status">
                    <!-- CONTENT TOURIST ITEM -->
                    <span class="body-group__drag-item icon-serie-drag"></span>
                    <div class="body-item list-group-item" :class="{'row-warning':serie_service.code==='' || serie_service.code===null}">
                      <div class="body-item__check">
                        <div class="check-content icon-resource--red">
                          <span class="check-content__add">
                            <span class="icon-plus-circle"></span>
                          </span>
                          <span class="check-content__edit">
                            <span class="icon-edit"></span>
                          </span>
                          <span class="check-content__delete">
                            <span class="icon-trash-2" @click="will_remove_service(serie_service)" v-show="!show_delete_multiple && !(serie_service.will_delete)"></span>
                            <span class="icon-trash-2" style="color: red;" @click="remove_services([serie_service.id])" v-show="!show_delete_multiple && serie_service.will_delete"></span>
                            <span class="styled-checkbox">
                              <input class="" type="checkbox" v-model="serie_service.check_delete"
                                     v-show="show_delete_multiple" @click="verify_btn_delete()">
                            </span>
                          </span>
                        </div>
                      </div>
                      <div class="body-item__itinerary">
                        <div class="itinerary-content pointer-event">
                          <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top">
                            <div class="detail-content__dropdown dropdown-tarifa" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <p class="itinerary-content__date" :inner-html.prop="serie_service.date | format_date_large"></p>
                              <div class="dropdown-container__content dropdown-menu" aria-labelledby="dropdown-publico">
                                <div class="container-content">
                                  <div class="content-info">
                                    <label class="d-block label-date" @click.stop="">
                                      <input @click.stop="" class="" type="radio" checked>
                                      Cambiar solo esta fecha
                                    </label>
                                    <label class="d-block label-date" @click.stop="">
                                      <input @click.stop="" class="" type="radio">
                                      Alterar fechas siguientes
                                    </label>
                                  </div>
                                  <div class="content-calender" @click.stop="">
                                    <b-calendar @click.stop="" v-model="calendary_value" :date-disabled-fn="dateDisabled" locale="en"></b-calendar>
                                  </div>
                                  <div class="float-right">
                                    <a href="#" class="link-cancel font-weight-bold mr-3" style="font-size: 1.3rem;">Cancelar</a>
                                    <a href="#" class="font-weight-bold" style="font-size: 1.3rem;">Aplicar</a>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>


                        </div>
                      </div>
                      <div class="body-item__duration">
                        <div class="duration-content">
                          <span class="duration-content__time" v-if="serie_service.type_service==='service'">{{ serie_service.duration }} hr.</span>
                          <span class="duration-content__time" v-if="serie_service.type_service==='hotel'">{{ serie_service.duration }} N.</span>
                        </div>
                      </div>
                      <div class="body-item__description">
                        <div class="description-content">
                          <div class="description-content__detail">
                            <p class="detail-content">
                              <span class="detail-content__destination without-code" v-if="serie_service.code==='' || serie_service.code===null">
                                (Sin código)
                              </span>
                              <span class="detail-content__destination" v-else>
                                [{{ serie_service.code }}]
                              </span>
                            <span :class="{'without-code':serie_service.code==='' || serie_service.code===null}" v-if="lang === 'es'">{{ serie_service.description_ES | filter_text }}</span>
                            <span :class="{'without-code':serie_service.code==='' || serie_service.code===null}" v-if="lang === 'en'">{{ serie_service.description_EN | filter_text }}</span>
                            <span :class="{'without-code':serie_service.code==='' || serie_service.code===null}" v-if="lang === 'pt'">{{ serie_service.description_PT | filter_text }}</span>
                            <span :class="{'without-code':serie_service.code==='' || serie_service.code===null}" v-if="lang === 'it'">{{ serie_service.description_IT | filter_text }}</span>
                            </p>
                            <a class="detail-components" v-if="serie_service.type_service==='service' && serie_service.type_code_service === 'parent'"
                               @click="serie_service.show_components=!(serie_service.show_components)">
                              Componentes<span class="detail-components__number"> ({{ serie_service.total_components }})</span>
                            </a>
                            <a class="detail-components" v-if="serie_service.type_service==='hotel' && serie_service.total_alternatives>0"
                               @click="serie_service.show_alternatives=!(serie_service.show_alternatives)">
                              Alternativas<span class="detail-components__number"> ({{ serie_service.total_alternatives }})</span>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="body-item__accommodation">
                        <div class="accommodation-content" v-if="serie_service.type_service==='service'">
                          <!-- ADD CLASS ITEM INACTIVE accommodation-content__pax--inactive -->
                          <div class="accommodation-content__pax">
                            <span class="pax-number" :class="{'accommodation-content__pax--inactive':serie_range_selected.min===0}">
                              {{ serie_range_selected.min }}
                            </span>
                            <span class="pax-name">PAX</span>
                          </div>
                          <div v-for="companion in serie_companions"
                               :class="{'accommodation-content__escort': companion.user_type.code !== 'T', 'accommodation-content__tc': companion.user_type.code === 'T'}">
                            <span :class="{'escort-number': companion.user_type.code !== 'T', 'tc-number': companion.user_type.code === 'T'}">{{ companion.quantity }}</span>
                            <span :class="{'escort-name': companion.user_type.code !== 'T', 'tc-name': companion.user_type.code === 'T'}">{{ companion.user_type.description | upper }}</span>
                          </div>
                        </div>
                        <div class="accommodation-content" v-if="serie_service.type_service==='hotel'">
                          <!-- ADD CLASS ITEM INACTIVE accommodation-content__pax--inactive -->
                          <div class="accommodation-content__pax">
                            <span class="pax-number accommodation-content__pax--inactive">
                              0
                            </span>
                            <span class="pax-name">SGL</span>
                          </div>
                          <div class="accommodation-content__escort">
                            <span class="escort-number accommodation-content__pax--inactive">
                              0
                            </span>
                            <span class="escort-name">DBL</span>
                          </div>
                          <div class="accommodation-content__tc">
                            <span class="tc-number accommodation-content__pax--inactive">
                              0
                            </span>
                            <span class="tc-name">TPL</span>
                          </div>
                        </div>
                      </div>
                      <div class="body-item__details">
                        <div class="detail-content pointer-event">

                          <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="Habitaciones"
                               v-if="serie_service.type_service==='hotel'">
                            <div class="detail-content__dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="icon-bed-double"></span>
                              <div class="dropdown-container__content dropdown-menu dropdown-tarifa" aria-labelledby="dropdown-publico">
                                <div class="container-content">
                                  <div class="d-flex justify-content-between">
                                    <h5>Habitaciones</h5>
                                    <span style="font-size: 1.3rem; font-weight: bold;">5</span>
                                  </div>
                                  <hr>
                                  <div class="d-flex justify-content-between">
                                    <p class="p-txt">Doble stantard</p>
                                    <input class="values-txt" type="number" value="2">
                                  </div>
                                  <div class="d-flex justify-content-between">
                                    <p class="p-txt">Doble matrimonial</p>
                                    <input class="values-txt" disabled type="number" value="1">
                                  </div>
                                  <div class="d-flex justify-content-between">
                                    <p class="p-txt">Doble matrimonial</p>
                                    <span class="span-txt">5</span>
                                  </div>
                                  <div class="float-right">
                                    <a href="#" class="font-weight-bold" style="font-size: 1.3rem;">Modificar <span class="icon-edit ml-1"></span></a>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="Compartido"
                               v-if="serie_service.type_service==='service'">
                            <div class="detail-content__dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="icon-globe"></span>
                              <div class="dropdown-container__content dropdown-menu dropdown-tipo" aria-labelledby="dropdown-publico">
                                <div class="container-content">
                                  <div class="container-content__change">
                                    <a class="change-txt" >Cambiar</a>
                                    <span class="icon-lock"></span>
                                  </div>
                                  <div class="container-content__change">
                                    <a class="change-txt" >Cambiar</a>
                                    <span class="icon-bed-double"></span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      <div class="body-item__released">
                        <!-- ADD CLASS ITEM INACTIVE release-content--inactive -->
                        <div class="release-content release-content--inactive">
                          <span class="release-content__detail">TC x 0</span>
                        </div>
                      </div>
                      <div class="body-item__released">

                        <div class="detail-content pointer-event">
                          <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="">

                            <div class="detail-content__dropdown dropdown-tarifa"
                                 v-if="serie_range_selected.min===0 || serie_service.prices.length===0">
                              <span class="font-size-10">S/.</span> <span class="font-weight-bold">-</span>
                            </div>

                            <div class="detail-content__dropdown dropdown-tarifa" v-for="price in serie_service.prices"
                                 v-if="price.status && price.serie_range_id===serie_range_selected.id">
                              <span class="font-size-10">S/.</span> <span class="font-weight-bold" :class="{'price-pop': serie_service.prices_in_range.length>1}"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ price.amount }}</span>
                              <div class="dropdown-container__content dropdown-menu" aria-labelledby="dropdown-publico">
                                <div class="container-content" v-if="serie_service.prices_in_range.length>1">
                                  <h5>Cambiar base de tarifas </h5>
                                  <div class="content-info content-info-scroll" v-show="serie_service.view_content_prices===1">
                                    <label class="d-block" v-for="price_in_range in serie_service.prices_in_range" @click.stop="">
                                      <input @click.stop="" class="" v-model="serie_service.price_radio_selected_id" :value="price_in_range.id"
                                             type="radio" :name="'radio_price_'+serie_service.id">
                                      [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                    </label>
                                  </div>
                                  <p v-show="serie_service.view_content_prices===2">Rangos y Fechas</p>
                                  <div class="content-info" v-show="serie_service.view_content_prices===2">

                                    <label class="d-block" @click.stop="">
                                      <input class="" @click.stop="" type="radio" value="1" :disabled="loading"
                                             :name="'radio_mode_prices_range_date_'+serie_service.id" v-model="radio_mode_prices_range_date">
                                      Aplicar sólo al rango y fecha seleccionada.
                                    </label>
                                    <label class="d-block" @click.stop="">
                                      <input class="" @click.stop="" type="radio" value="2"  :disabled="loading"
                                             :name="'radio_mode_prices_range_date_'+serie_service.id" v-model="radio_mode_prices_range_date">
                                      Sólo al rango seleccionado y todas las fechas.
                                    </label>
                                    <label class="d-block" @click.stop="">
                                      <input class="" @click.stop="" type="radio" value="3"  :disabled="loading"
                                             :name="'radio_mode_prices_range_date_'+serie_service.id" v-model="radio_mode_prices_range_date">
                                      Sólo a la fecha seleccionada y todos los rangos.
                                    </label>
                                    <label class="d-block" @click.stop="">
                                      <input class="" @click.stop="" type="radio" value="4"  :disabled="loading"
                                             :name="'radio_mode_prices_range_date_'+serie_service.id" v-model="radio_mode_prices_range_date">
                                      Todos los rangos y todas las fechas.
                                    </label>
                                  </div>

                                  <p v-show="serie_service.view_content_prices===2 && serie_categories.length>1">Categorías</p>
                                  <div class="content-info" @click.stop="" v-show="serie_service.view_content_prices===2 && serie_categories.length>1">
                                    <label class="d-block">
                                      <input class="" type="radio" value="1" :disabled="loading"
                                             :name="'radio_mode_prices_category_'+serie_service.id" v-model="radio_mode_prices_category">
                                      Sólo a la categoría actual.
                                    </label>
                                    <label class="d-block">
                                      <input class="" type="radio" value="2" :disabled="loading"
                                             :name="'radio_mode_prices_category_'+serie_service.id" v-model="radio_mode_prices_category">
                                      Aplicar a todas las categorías.
                                    </label>
                                  </div>

                                  <div class="alert alert-info" v-show="serie_service.view_content_prices===2">
                                    <i class="fa fa-info-circle"></i> Se recomienda utilizar esta opción después de confirmar que se importaron todas las tarifas en todas sus fechas y rangos solicitados.
                                  </div>
                                  <div class="float-right" v-show="serie_service.view_content_prices===1">
                                    <a href="javascript:;" @click.stop="serie_service.view_content_prices=2">Siguiente</a>
                                  </div>
                                  <div class="float-right" v-show="serie_service.view_content_prices===2 && loading">
                                    <i class="fa fa-spin fa-spinner"></i>
                                  </div>
                                  <div class="float-right" v-show="serie_service.view_content_prices===2 && !loading">
                                    <a href="javascript:;" @click.stop="change_price_status(serie_service)">Aplicar</a>
                                  </div>
                                  <div class="float-right" v-show="serie_service.view_content_prices===2 && !loading" style="margin-right: 15px;">
                                    <a href="javascript:;" @click.stop="serie_service.view_content_prices=1">< Atrás</a>
                                  </div>
                                </div>

                                <div class="container-content" v-else>
                                  <h5>Ninguna tarifa adicional</h5>
                                  <div class="content-info">
                                    <label class="d-block" v-for="price_in_range in serie_service.prices_in_range">
                                      <input class="" type="radio" checked>
                                      [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                    </label>
                                  </div>
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>

                      <div class="body-item__state">
                        <!-- ADD CLASS ITEM CONFIRMED no class -->
                        <!-- ADD CLASS ITEM PENDING state-content--pending -->
                        <!-- ADD CLASS ITEM ORDER state-content--order -->
                        <!-- ADD CLASS ITEM OBSERVED state-content--observed -->
                        <div class="state-content ">
                          <span class="content-detail detail-content_state state-content--pending">PENDIENTE</span>
                        </div>
                      </div>
                    </div>
                    <!-- CONTENT TOURIST SUB-ITEM -->
                    <div class="detail-collapse collapse" :class="{'show':serie_service.show_components}">
                      <li class="body-item body-item--collapse list-group-item"
                          v-for="serie_service_component in serie_services" v-if="serie_service_component.parent_id===serie_service.id">
                        <div class="body-item__check body-item__check--collapse">
                          <div class="check-content">
                            <span class="check-content__edit">
                                <i class="icon-resource"></i>
                            </span>
                            <span class="check-content__delete">
                                <i class="icon-resource"></i>
                            </span>
                          </div>
                        </div>
                        <div class="body-item__itinerary body-item__itinerary--collapse">
                          <div class="itinerary-content">
                            <p class="itinerary-content__date">componente</p>
                          </div>
                        </div>
                        <div class="body-item__duration body-item__duration--collapse">
                          <div class="duration-content">
                            <span class="duration-content__time">-</span>
                          </div>
                        </div>
                        <div class="body-item__description body-item__description--collapse">
                          <div class="description-content description-content--collapse">
                            <div class="description-content__detail description-content__detail--collapse">
                              <p class="detail-content detail-content--collapse" v-if="lang==='es'">
                                <span class="detail-content__destination">[{{ serie_service_component.code }}]</span>
                                {{ serie_service_component.description_ES | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='en'">
                                <span class="detail-content__destination">[{{ serie_service_component.code }}]</span>
                                {{ serie_service_component.description_EN | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='pt'">
                                <span class="detail-content__destination">[{{ serie_service_component.code }}]</span>
                                {{ serie_service_component.description_PT | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='it'">
                                <span class="detail-content__destination">[{{ serie_service_component.code }}]</span>
                                {{ serie_service_component.description_IT | filter_text  }}
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="body-item__accommodation body-item__accommodation--collapse">
                          <div class="accommodation-content accommodation-content--collapse">
                            <div class="accommodation-content__pax accommodation-content__pax--collapse">
                              <span class="pax-name pax-name--collapse">-</span>
                            </div>
                          </div>
                        </div>
                        <div class="body-item__details body-item__details--collapse">
                          <div class="detail-content detail-content--collapse">
                            <span class="detail-content__name detail-content__name--collapse">-</span>
                          </div>
                        </div>
                        <div class="body-item__released detail-content pointer-event">

                            <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="">

                              <div class="detail-content__dropdown dropdown-tarifa"
                                   v-if="serie_range_selected.min===0 || serie_service.prices.length===0">
                                <span class="font-size-10">S/.</span> <span class="font-size-13">-</span>
                              </div>

                              <div class="detail-content__dropdown dropdown-tarifa" v-for="price in serie_service_component.prices"
                                   v-if="price.status && price.serie_range_id===serie_range_selected.id">
                                <span class="font-size-10">S/.</span> <span class="font-size-13" :class="{'price-pop': serie_service_component.prices_in_range.length>1}"
                                          data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ price.amount }}</span>
                                <div class="dropdown-container__content dropdown-menu" aria-labelledby="dropdown-publico">
                                  <div class="container-content" v-if="serie_service_component.prices_in_range.length>1">
                                    <h5>Cambiar base de tarifas </h5>
                                    <div class="content-info content-info-scroll" v-show="serie_service_component.view_content_prices===1">
                                      <label class="d-block" v-for="price_in_range in serie_service_component.prices_in_range" @click.stop="">
                                        <input @click.stop="" class="" v-model="serie_service_component.price_radio_selected_id" :value="price_in_range.id"
                                               type="radio" :name="'radio_price_'+serie_service_component.id">
                                        [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                      </label>
                                    </div>
                                    <p v-show="serie_service_component.view_content_prices===2">Rangos y Fechas</p>
                                    <div class="content-info" v-show="serie_service_component.view_content_prices===2">

                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="1" :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_component.id" v-model="radio_mode_prices_range_date">
                                        Aplicar sólo al rango y fecha seleccionada.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="2" :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_component.id" v-model="radio_mode_prices_range_date">
                                        Sólo al rango seleccionado y todas las fechas.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="3" :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_component.id" v-model="radio_mode_prices_range_date">
                                        Sólo a la fecha seleccionada y todos los rangos.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="4" :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_component.id" v-model="radio_mode_prices_range_date">
                                        Todos los rangos y todas las fechas.
                                      </label>
                                    </div>

                                    <p v-show="serie_service_component.view_content_prices===2 && serie_categories.length>1">Categorías</p>
                                    <div class="content-info" @click.stop="" v-show="serie_service_component.view_content_prices===2 && serie_categories.length>1">
                                      <label class="d-block">
                                        <input class="" type="radio" value="1" :disabled="loading"
                                               :name="'radio_mode_prices_category_'+serie_service_component.id" v-model="radio_mode_prices_category">
                                        Sólo a la categoría actual.
                                      </label>
                                      <label class="d-block">
                                        <input class="" type="radio" value="2" :disabled="loading"
                                               :name="'radio_mode_prices_category_'+serie_service_component.id" v-model="radio_mode_prices_category">
                                        Aplicar a todas las categorías.
                                      </label>
                                    </div>

                                    <div class="alert alert-info" v-show="serie_service_component.view_content_prices===2">
                                      <i class="fa fa-info-circle"></i> Se recomienda utilizar esta opción después de confirmar que se importaron todas las tarifas en todas sus fechas y rangos solicitados.
                                    </div>
                                    <div class="float-right" v-show="serie_service_component.view_content_prices===1">
                                      <a href="javascript:;" @click.stop="serie_service_component.view_content_prices=2">Siguiente</a>
                                    </div>
                                    <div class="float-right" v-show="serie_service_component.view_content_prices===2 && loading">
                                      <i class="fa fa-spin fa-spinner"></i>
                                    </div>
                                    <div class="float-right" v-show="serie_service_component.view_content_prices===2 && !loading">
                                      <a href="javascript:;" @click.stop="change_price_status(serie_service_component)">Aplicar</a>
                                    </div>
                                    <div class="float-right" v-show="serie_service_component.view_content_prices===2 && !loading" style="margin-right: 15px;">
                                      <a href="javascript:;" @click.stop="serie_service_component.view_content_prices=1">< Atrás</a>
                                    </div>
                                  </div>

                                  <div class="container-content" v-else>
                                    <h5>Ninguna tarifa adicional</h5>
                                    <div class="content-info">
                                      <label class="d-block" v-for="price_in_range in serie_service_component.prices_in_range">
                                        <input class="" type="radio" checked>
                                        [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                      </label>
                                    </div>
                                  </div>
                                </div>
                              </div>

                            </div>

                        </div>
                        <div class="body-item__state">
                          <div class="state-content">
                          </div>
                        </div>
                      </li>
                    </div>

                    <div class="detail-collapse collapse" :class="{'show':serie_service.show_alternatives}">
                      <li class="body-item body-item--collapse list-group-item"
                          v-for="serie_service_alternative in serie_services"
                          v-if="serie_service_alternative.type_service==='hotel' &&
                          !(serie_service_alternative.status) && serie_service_alternative.date===serie_service.date">
                        <div class="body-item__check body-item__check--collapse">
                          <div class="check-content check-content--collapse">
                            <span class="check-content__draggable">
                                <i class="icon-resource"></i>
                            </span>
                            <span class="check-content__delete">
                                <i class="icon-resource"></i>
                            </span>
                          </div>
                        </div>
                        <div class="body-item__itinerary body-item__itinerary--collapse">
                          <div class="itinerary-content">
                            <p class="itinerary-content__date">alternativa</p>
                          </div>
                        </div>
                        <div class="body-item__duration body-item__duration--collapse">
                          <div class="duration-content">
                            <span class="duration-content__time">-</span>
                          </div>
                        </div>
                        <div class="body-item__description body-item__description--collapse">
                          <div class="description-content description-content--collapse">
                            <div class="description-content__detail description-content__detail--collapse">
                              <p class="detail-content detail-content--collapse" v-if="lang==='es'">
                                <span class="detail-content__destination">[{{ serie_service_alternative.code }}]</span>
                                {{ serie_service_alternative.description_ES | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='en'">
                                <span class="detail-content__destination">[{{ serie_service_alternative.code }}]</span>
                                {{ serie_service_alternative.description_EN | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='pt'">
                                <span class="detail-content__destination">[{{ serie_service_alternative.code }}]</span>
                                {{ serie_service_alternative.description_PT | filter_text }}
                              </p>
                              <p class="detail-content detail-content--collapse" v-if="lang==='it'">
                                <span class="detail-content__destination">[{{ serie_service_alternative.code }}]</span>
                                {{ serie_service_alternative.description_IT | filter_text  }}
                              </p>
                            </div>
                          </div>
                        </div>

                        <div class="body-item__accommodation">
                          <div class="accommodation-content">
                            <!-- ADD CLASS ITEM INACTIVE accommodation-content__pax--inactive -->
                            <div class="accommodation-content__pax">
                            <span class="pax-number accommodation-content__pax--inactive">
                              0
                            </span>
                              <span class="pax-name">SGL</span>
                            </div>
                            <div class="accommodation-content__escort">
                            <span class="escort-number accommodation-content__pax--inactive">
                              0
                            </span>
                              <span class="escort-name">DBL</span>
                            </div>
                            <div class="accommodation-content__tc">
                            <span class="tc-number accommodation-content__pax--inactive">
                              0
                            </span>
                              <span class="tc-name">TPL</span>
                            </div>
                          </div>
                        </div>
                        <div class="body-item__details">
                          <div class="detail-content pointer-event">
                            <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="Habitaciones">
                              <div class="detail-content__dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="icon-bed-double"></span>
                                <div class="dropdown-container__content dropdown-menu dropdown-tarifa" aria-labelledby="dropdown-publico">
                                  <div class="container-content">
                                    <div class="d-flex justify-content-between">
                                      <h5>Habitaciones</h5>
                                      <span style="font-size: 1.3rem; font-weight: bold;">5</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                      <p class="p-txt">Doble stantard</p>
                                      <input class="values-txt" type="number" value="2">
                                    </div>
                                    <div class="d-flex justify-content-between">
                                      <p class="p-txt">Doble matrimonial</p>
                                      <input class="values-txt" disabled type="number" value="1">
                                    </div>
                                    <div class="d-flex justify-content-between">
                                      <p class="p-txt">Doble matrimonial</p>
                                      <span class="span-txt">5</span>
                                    </div>
                                    <div class="float-right">
                                      <a href="#" class="font-weight-bold" style="font-size: 1.3rem;">Modificar <span class="icon-edit ml-1"></span></a>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="body-item__released">
                          <!-- ADD CLASS ITEM INACTIVE release-content--inactive -->
                          <div class="release-content release-content--inactive">
                            <span class="release-content__detail">TC x 0</span>
                          </div>
                        </div>
                        <div class="body-item__released">

                          <div class="detail-content pointer-event">
                            <div class="detail-content__tooltip" data-toggle="tooltip" data-placement="top" title="">

                              <div class="detail-content__dropdown dropdown-tarifa"
                                   v-if="serie_range_selected.min===0 || serie_service_alternative.prices.length===0">
                                <span class="font-size-10">S/.</span> <span class="font-weight-bold">-</span>
                              </div>

                              <div class="detail-content__dropdown dropdown-tarifa" v-for="price in serie_service_alternative.prices"
                                   v-if="price.status && price.serie_range_id===serie_range_selected.id">
                                <span class="font-size-10">S/.</span> <span class="font-weight-bold" :class="{'price-pop': serie_service_alternative.prices_in_range.length>1}"
                                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ price.amount }}</span>
                                <div class="dropdown-container__content dropdown-menu" aria-labelledby="dropdown-publico">
                                  <div class="container-content" v-if="serie_service_alternative.prices_in_range.length>1">
                                    <h5>Cambiar base de tarifas </h5>
                                    <div class="content-info content-info-scroll" v-show="serie_service_alternative.view_content_prices===1">
                                      <label class="d-block" v-for="price_in_range in serie_service_alternative.prices_in_range" @click.stop="">
                                        <input @click.stop="" class="" v-model="serie_service_alternative.price_radio_selected_id" :value="price_in_range.id"
                                               type="radio" :name="'radio_price_'+serie_service_alternative.id">
                                        [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                      </label>
                                    </div>
                                    <p v-show="serie_service_alternative.view_content_prices===2">Rangos y Fechas</p>
                                    <div class="content-info" v-show="serie_service_alternative.view_content_prices===2">

                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="1" :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_alternative.id" v-model="radio_mode_prices_range_date">
                                        Aplicar sólo al rango y fecha seleccionada.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="2"  :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_alternative.id" v-model="radio_mode_prices_range_date">
                                        Sólo al rango seleccionado y todas las fechas.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="3"  :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_alternative.id" v-model="radio_mode_prices_range_date">
                                        Sólo a la fecha seleccionada y todos los rangos.
                                      </label>
                                      <label class="d-block" @click.stop="">
                                        <input class="" @click.stop="" type="radio" value="4"  :disabled="loading"
                                               :name="'radio_mode_prices_range_date_'+serie_service_alternative.id" v-model="radio_mode_prices_range_date">
                                        Todos los rangos y todas las fechas.
                                      </label>
                                    </div>

                                    <p v-show="serie_service_alternative.view_content_prices===2 && serie_categories.length>1">Categorías</p>
                                    <div class="content-info" @click.stop="" v-show="serie_service_alternative.view_content_prices===2 && serie_categories.length>1">
                                      <label class="d-block">
                                        <input class="" type="radio" value="1" :disabled="loading"
                                               :name="'radio_mode_prices_category_'+serie_service_alternative.id" v-model="radio_mode_prices_category">
                                        Sólo a la categoría actual.
                                      </label>
                                      <label class="d-block">
                                        <input class="" type="radio" value="2" :disabled="loading"
                                               :name="'radio_mode_prices_category_'+serie_service_alternative.id" v-model="radio_mode_prices_category">
                                        Aplicar a todas las categorías.
                                      </label>
                                    </div>

                                    <div class="alert alert-info" v-show="serie_service_alternative.view_content_prices===2">
                                      <i class="fa fa-info-circle"></i> Se recomienda utilizar esta opción después de confirmar que se importaron todas las tarifas en todas sus fechas y rangos solicitados.
                                    </div>
                                    <div class="float-right" v-show="serie_service_alternative.view_content_prices===1">
                                      <a href="javascript:;" @click.stop="serie_service_alternative.view_content_prices=2">Siguiente</a>
                                    </div>
                                    <div class="float-right" v-show="serie_service_alternative.view_content_prices===2 && loading">
                                      <i class="fa fa-spin fa-spinner"></i>
                                    </div>
                                    <div class="float-right" v-show="serie_service_alternative.view_content_prices===2 && !loading">
                                      <a href="javascript:;" @click.stop="change_price_status(serie_service_alternative)">Aplicar</a>
                                    </div>
                                    <div class="float-right" v-show="serie_service_alternative.view_content_prices===2 && !loading" style="margin-right: 15px;">
                                      <a href="javascript:;" @click.stop="serie_service_alternative.view_content_prices=1">< Atrás</a>
                                    </div>
                                  </div>

                                  <div class="container-content" v-else>
                                    <h5>Ninguna tarifa adicional</h5>
                                    <div class="content-info">
                                      <label class="d-block" v-for="price_in_range in serie_service_alternative.prices_in_range">
                                        <input class="" type="radio" checked>
                                        [{{ price_in_range.base_code }}] {{ price_in_range.base_name }} (S/.{{price_in_range.amount}})
                                      </label>
                                    </div>
                                  </div>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>

                        <div class="body-item__state">
                          <!-- ADD CLASS ITEM PENDING state-content--pending -->
                          <!-- ADD CLASS ITEM ORDER state-content--order -->
                          <!-- ADD CLASS ITEM OBSERVED state-content--observed -->
                          <div class="state-content ">
                            <span class="content-detail detail-content_state state-content--pending">PENDIENTE</span>
                          </div>
                        </div>
                      </li>
                    </div>

                  </div>
                </div>

              </div>

            </div>
          </div>
          <!-- BUTTON -->
          <div class="content-button">
            <div class="content-button__group content-button__group--selection">
              <div class="content-ordes" v-show="show_delete_multiple">
                <button type="button" class="content-ordes__button btn btn-primary" @click="will_remove_services()" :disabled="btn_delete_multiple">
                  <i class="icon-trash-all icon-resource--alternative icon-serie-eliminar"></i>
                  <span class="button-txt">Eliminar</span>
                </button>
              </div>
            </div>
            <div class="content-button__group">
<!--              Modal de Pedidos-->
              <div class="content-ordes">
                <button class="content-ordes__button content-ordes__button--disabled btn btn-primary"
                        data-toggle="modal" data-target="#modal_ordes" data-backdrop="static"
                        @click="toggleModal( 0, 'orders', { translations: translations } )">
                  <i class="icon-inbox icon-resource--alternative icon-serie-pedidos"></i>
                  <span class="button-txt">Pedidos</span>
                </button>
              </div>
<!--              Modal Cotizaciones-->
              <div class="content-quotes">
                <div class="content-quotes__button content-quotes__button--disabled btn btn-primary" data-toggle="modal" data-target="#modal_quotes" data-backdrop="static">
                  <i class="icon-folder icon-resource--alternative icon-serie-cotizar"></i>
                  <span class="button-txt">Cotizaciones</span>
                </div>
                <div class="content-quotes__modal modal fade" id="modal_quotes" tabindex="-1" role="dialog" aria-labelledby="modal_quotesLabel" aria-hidden="true">
                  <div class="modal-content-quotes modal-dialog modal-dialog__custom" role="document">
                    <div class="modal-content modal-content__container">

                      <!-- PRIMERA PANTALLA -->
                      <div class="container-content container-content--2">
                        <a class="container-content__close" data-dismiss="modal">Cerrar <span class="close-action">X</span></a>
                        <h2 class="container-content__title">Cotizaciones</h2>
                        <div class="container-content__destination-d">
                          <span class="container-content__destination">Grupo para Cusco <span class="destination-agency">(Lima Tours)</span></span>
                          <a href="#" class="btn-section">ver resumen</a>
                        </div>
                        <div class="container-content__details2">
                          <span class="details-date">Fecha seleccionada</span>
                          <input class="details-text" type="text" placeholder="vie 05/11/2021   -  jue, 011/11/2021  -  6N  -  $945 (mejor tarifa)">
                          <i class="details-ico icon-resource--alternative icon-serie-cotizar"></i>
                          <i class="details-ico icon-resource--alternative icon-serie-rango"></i>
                          <i class="details-ico icon-resource--alternative icon-serie-rango"></i>
                          <i class="details-ico icon-resource--alternative icon-serie-cotizar"></i>
                        </div>
                        <div class="container-content__message2">
                          <i class="message-ico icon-resource--alternative icon-serie-cotizar"></i>
                          <span class="message-txt">Esta salida tiene la tarifa más económica!</span>
                        </div>

                        <div class="content-tab--header content-tab--header2">
                          <div class="content-tab--header__menu">
                            <ul class="menu-client menu-client nav nav-tabs">
                              <li class="menu-client__list">
                                <a class="list-item list-item--tourist nav-link active" data-toggle="tab" href="#tourist3" style="pointer-events: none">Turista</a>
                              </li>
                              <li class="menu-client__list">
                                <a class="list-item list-item--superior-tourist nav-link" data-toggle="tab" href="#superior_tourist3" style="pointer-events: none">Turista Superior</a>
                              </li>
                              <li class="menu-client__list">
                                <a class="list-item list-item--first nav-link" data-toggle="tab" href="#first3" style="pointer-events: none">Primera</a>
                              </li>
                              <li class="menu-client__list">
                                <a class="list-item list-item--first nav-link" data-toggle="tab" href="#first-superior3" style="pointer-events: none">Primera Superior</a>
                              </li>
                            </ul>
                          </div>
                        </div>
                        <div class="content-tab--body content-tab--body2">
                          <div class="content-tab--body__container">
                            <div id="tourist3" class="container-content container-content-table container-content--modified2 tab-pane active">
                              <div id="accordionTourist3" class="container-content__table">
                                <ul class="table-list">
                                  <li class="table-list__item">
                                    <span class="item-txt item-txt--m item-txt--t">RANGOS</span>
                                    <span class="item-txt item-txt--m">SGL</span>
                                    <span class="item-txt item-txt--m">DBL</span>
                                    <span class="item-txt item-txt--m">TPL</span>
                                    <span class="item-txt item-txt--m">QUA</span>
                                    <span class="item-txt item-txt--m">TC</span>
                                    <span class="item-txt item-txt--m">ESCORT</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">1</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">5</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">10</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">20</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">30</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">50</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">75</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">99</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                  <li class="table-list__item table-list__item--bg">
                                    <span class="item-txt item-txt--title">150</span>
                                    <span class="item-txt">800</span>
                                    <span class="item-txt">700</span>
                                    <span class="item-txt">667</span>
                                    <span class="item-txt">-</span>
                                    <span class="item-txt">360</span>
                                    <span class="item-txt">360</span>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <div id="superior_tourist3" class="container-content tab-pane fade">
                            </div>
                            <div id="first3" class="container-content tab-pane fade">
                            </div>
                            <div id="first_superior3" class="container-content tab-pane fade">
                            </div>
                          </div>
                        </div>
                        <div class="container-content___buttons">
                          <div class="content-export__button btn btn-primary">
                            <i class="buttton-ico icon-serie-cotizar"></i>
                            <span class="button-txt">Exportar Excel</span>
                          </div>
                          <div class="content-generate__button btn btn-primary">
                            <i class="buttton-ico icon-serie-cotizar"></i>
                            <span class="button-txt">Generar File</span>
                          </div>
                        </div>
                      </div>

                      <!-- SEGUNDA PANTALLA -->
                      <div class="container-content">
                        <a class="container-content__close" data-dismiss="modal">Cerrar <span class="close-action">X</span></a>
                        <h2 class="container-content__title">Crear File</h2>
                        <span class="container-content__destination">Grupo para Cusco<span class="destination-agency">(Lima Tours)</span></span>
                        <div class="container-content__details" style="opacity: 0;">
                          <div class="details-left">
                            <p class="container-content__detail">Selecciona una fecha de salida para ver con detalles los precios que la componen.</p>
                            <span class="container-content__price">Tarifa más económica (1/Turista)<i class="price-icon icon-resource--alternative icon-serie-historial"></i></span>
                          </div>
                          <div class="details-right">
                            <i class="details-right__ico icon-resource--alternative icon-serie-cotizar"></i>
                            <a class="details-right__txt">Comparar Tarifas</a>
                          </div>
                        </div>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Octubre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group list-item__group--mod ">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group list-item__group--mod ">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Noviembre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group list-item__group--alternative">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group list-item__group--mod ">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Diciembre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <div class="container-content___buttons">
                          <div class="content-generate__button btn btn-primary">
                            <i class="buttton-ico icon-serie-cotizar"></i>
                            <span class="button-txt">Crear</span>
                          </div>
                        </div>
                      </div>

                      <!-- TERCERA PANTALLA -->
                      <div class="container-content">
                        <a class="container-content__close" data-dismiss="modal">Cerrar <span class="close-action">X</span></a>
                        <h2 class="container-content__title">Cotizaciones</h2>
                        <span class="container-content__destination">Grupo para Cusco<span class="destination-agency">(Lima Tours)</span></span>
                        <div class="container-content__details">
                          <div class="details-left">
                            <p class="container-content__detail">Selecciona una fecha de salida para ver con detalles los precios que la componen.</p>
                            <span class="container-content__price">Tarifa más económica (1/Turista)<i class="price-icon icon-resource--alternative icon-serie-historial"></i></span>
                          </div>
                          <div class="details-right">
                            <i class="details-right__ico icon-resource--alternative icon-serie-cotizar"></i>
                            <a class="details-right__txt">Comparar Tarifas</a>
                          </div>
                        </div>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Octubre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Noviembre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group list-item__group--alternative">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <ul class="container-content__list">
                          <li class="list-item">
                            <span class="list-item__month">Diciembre / 2021</span>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                          <li class="list-item">
                            <p class="list-item__group">sab, <span class="group-bold">28/10</span> - <span class="group-gray">$1000</span></p>
                            <p class="list-item__group">dom, <span class="group-bold">29/10</span> - <span class="group-gray">$1100</span></p>
                            <p class="list-item__group">lun, <span class="group-bold">30/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mar, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                            <p class="list-item__group">mie, <span class="group-bold">31/10</span> - <span class="group-gray">$1150</span></p>
                          </li>
                        </ul>
                        <div class="container-content___buttons">
                          <div class="content-export__button btn btn-primary">
                            <i class="buttton-ico icon-serie-cotizar"></i>
                            <span class="button-txt">Exportar Excel</span>
                          </div>
                          <div class="content-generate__button btn btn-primary">
                            <i class="buttton-ico icon-serie-cotizar"></i>
                            <span class="button-txt">Generar File</span>
                          </div>
                        </div>
                      </div>




                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>

<!--        -->

      </div>
      <component ref="template" v-bind:is="modal" v-bind:data="dataModal"></component>

    </div>
</template>
<script>
    export default {
        props: ['translations','serie_id'],
        data: () => {
            return {
                lang: '',
                loading: false,
                baseExternalURL: window.baseExternalURL,
                baseURL: window.baseURL,
                calendary_value: '',
                serie_id_decode:'',
                serie: {
                    user : {}
                },
                modal: '',
                dataModal: {},
                total_notes: 0,
                total_departures: 0,
                code: "",
                user_id: "",
                user_invited: false,
                time_for_guest: 0,
                type_permission: 1, // lectura
                categories: [],
                serie_categories: [],
                serie_companions: [],
                serie_ranges: [],
                serie_departures: [],
                serie_departure_id: null,
                show_drop_departures: false,
                show_drop_categories: false,
                show_drop_ranges: false,
                show_drop_rates: false,
                show_delete_departures: false,
                total_delete_departures: 0,
                check_all_categories: false,
                companions: [],
                pay_modes: [],
                ranges: [
                    {
                        id : null,
                        min : 5,
                        max : null,
                        free_scort : 0,
                        free_tc : 0,
                        error : false
                    }
                ],
                rate_protection : null,
                serie_services : [],
                serie_range_selected : {
                    min : 0
                },
                serie_departure_selected : {
                },
                serie_category_id_choose : null,
                show_delete_multiple:false,
                btn_delete_multiple:true,
                radio_mode_prices_range_date:1,
                radio_mode_prices_category:1,
            }
        },
        computed: {
        },
        mounted: function() {

            this.lang = localStorage.getItem('lang')
            this.code = localStorage.getItem('code')
            this.user_id = parseInt( localStorage.getItem('user_id') )
            this.user_invited = ( this.code === 'guest' )

            try {
                this.serie_id_decode = atob(this.serie_id)
            } catch(e){
                window.location = '/error'
            }

            this.search()
            this.get_total_notes()
            this.get_serie_departures()
            this.get_serie_categories()
            this.get_serie_companions()
            this.get_serie_ranges()
            this.search_categories()
            this.get_companion_user_types()
            this.get_pay_modes()
            this.get_rate_protection()
            this.get_serie_services()
        },
        methods: {
          dateDisabled(ymd, date) {
            // Disable weekends (Sunday = `0`, Saturday = `6`) and
            // disable days that fall on the 13th of the month
            const weekday = date.getDay()
            const day = date.getDate()
            // Return `true` if the date should be disabled
            return weekday === 0 || weekday === 6 || day === 13
          },

            change_price_status(serie_service){
                this.loading = true
                let data = {
                    serie_id : this.serie_id_decode,
                    serie_category_id : this.serie_category_id_choose,
                    serie_range_id : this.serie_range_selected.id,
                    date : serie_service.date,
                    serie_service_id : serie_service.id,
                    serie_service_code : serie_service.code,
                    serie_service_price_id_selected : serie_service.price_radio_selected_id,
                    option_range_date : this.radio_mode_prices_range_date,
                    option_category : this.radio_mode_prices_category,
                }
                axios.put(
                    baseExternalURL + 'api/series/categories/services/prices/status', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            serie_service.view_content_prices=1
                            this.get_serie_services()
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            verify_btn_delete(){

                let me = this

                setTimeout( ()=>{
                    let count_ = 0
                    me.serie_services.forEach(s=>{
                        if( s.check_delete ){
                            count_++
                        }
                    })

                    if( count_ > 0 ){
                        this.btn_delete_multiple = false
                    } else {
                        this.btn_delete_multiple = true
                    }
                }, 500 )

            },
            will_remove_service(serie_service){
                serie_service.will_delete = true
                setTimeout( ()=>{
                    serie_service.will_delete = false
                }, 5500)
            },
            will_remove_services(){
                let services_ids = []
                this.serie_services.forEach(s=>{
                    if( s.check_delete ){
                        services_ids.push(s.id)
                    }
                })
                this.remove_services(services_ids)
            },
            remove_services(serie_services_ids){
                this.loading = true
                let data = {
                    serie_services_ids : serie_services_ids
                }
                axios.delete(
                    baseExternalURL + 'api/series/categories/services', {data}
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.properly_removed, {
                                position: 'top-right'
                            })
                            this.get_serie_services()
                            this.btn_delete_multiple = false
                            this.show_delete_multiple = false
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            set_category_id_active( serie_category_id ){
                this.serie_category_id_choose = serie_category_id
                this.get_serie_services(serie_category_id)
            },
            get_prices(){

                this.loading = true

                let data = {
                    serie_range_id : this.serie_range_selected.id,
                    date : this.serie_departure_selected.value
                }

                axios.post(
                    baseExternalURL + 'api/series/categories/'+ this.serie_category_id_choose +'/services/prices', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.get_serie_services()
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            will_get_prices(){
                let me = this
                setTimeout( function(){

                    if( me.serie_range_selected.min !== 0 ){
                        me.serie_services.forEach( service=>{
                            service.prices_in_range = []
                            service.price_radio_selected_id = null
                                service.prices.forEach( price_=>{
                                if( price_.serie_range_id === me.serie_range_selected.id ){
                                    service.prices_in_range.push(price_)
                                    if( price_.status ){
                                        service.price_radio_selected_id = price_.id
                                    }
                                }
                            })
                        })
                    }

                    if( me.serie_range_selected.min === 0 || me.serie_departure_selected.value === undefined ){
                        return
                    }
                    me.get_prices()
                }, 500 )
            },
            get_serie_services( serie_category_id=null ){

                if( serie_category_id === null ){
                    serie_category_id = this.serie_category_id_choose
                }

                this.loading = true

                axios.get(
                    baseExternalURL + 'api/series/categories/'+ serie_category_id +'/services'
                )
                    .then((result) => {
                        if( result.data.success ){

                            let hotel_dates = []
                            // Para normalizar status de hoteles y alternativas
                            // ya que cuando importa de hoja master, pueden venir 2 hoteles en la misma fecha
                            // y ninguno con status 1
                            result.data.data.forEach( service_=>{
                                if( service_.type_service === 'hotel' ){
                                    if( hotel_dates[service_.date] === undefined ){
                                        hotel_dates[service_.date] = []
                                        hotel_dates[service_.date][0] = service_
                                    } else {
                                        hotel_dates[service_.date].push(service_)
                                    }
                                }
                            })
                            result.data.data.forEach( service_=>{
                                if( service_.type_service === 'hotel' ){
                                    let h_active_id = null
                                    hotel_dates[service_.date].forEach( h=>{
                                        if(h.status){
                                            h_active_id = h.id
                                        }
                                    })
                                    if( h_active_id === null ){
                                        hotel_dates[service_.date][0].status = 1
                                    }
                                }
                            })
                            console.log(hotel_dates)
                            // Para normalizar status de hoteles y alternativas

                            result.data.data.forEach( service=>{
                                service.view_content_prices = 1
                                service.show_components = false
                                service.show_alternatives = false
                                service.check_delete = false
                                service.will_delete = false
                                service.total_components = 0
                                if( service.type_code_service === 'parent' ){
                                    result.data.data.forEach( service_=>{
                                        if( service_.type_code_service === 'component' &&
                                            service_.parent_id === service.id ){
                                            service.total_components++
                                        }
                                    })
                                }
                                service.prices_in_range = []
                                service.price_radio_selected_id = null
                                if( this.serie_range_selected.min !== 0 ){
                                    service.prices.forEach( price_=>{
                                        if( price_.serie_range_id === this.serie_range_selected.id ){
                                            service.prices_in_range.push(price_)
                                            if( price_.status ){
                                                service.price_radio_selected_id = price_.id
                                            }
                                        }
                                    })
                                }

                                service.total_alternatives = 0
                                if( service.type_service === 'hotel' ){
                                    if( hotel_dates[service.date].length > 1 ){
                                        service.total_alternatives = hotel_dates[service.date].length - 1
                                        hotel_dates[service.date].forEach( h=>{
                                            if(h.id === service.id){
                                                service.status = h.status
                                            }
                                        })
                                    } else {
                                        service.status = 1
                                    }
                                }

                            })

                            this.serie_services = result.data.data
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            get_rate_protection(){

                let year_ = moment().format('YYYY')

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/rate_protections/year/'+year_
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.rate_protection = result.data.data
                        } else {
                            this.rate_protection = null
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save_ranges(){
                this.ranges.sort(function(a, b){
                    return a.min - b.min;
                })
                this.verify_ranges_errors()
                let errors_ = 0
                this.ranges.forEach( (range, r_k)=>{
                    if( range.error ){
                        errors_++
                    }
                    range.min = parseInt( range.min )
                    if( this.ranges[r_k+1] !== undefined ){
                        range.max = parseInt( this.ranges[r_k+1].min ) - 1
                    } else {
                        range.max = null
                    }
                })

                if( errors_ > 0 ){
                    this.$toast.warning("No puede ingresar rangos iguales", {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                axios.post(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/ranges', { ranges : this.ranges }
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.get_serie_ranges()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })

            },
            verify_ranges_errors(){
                this.ranges.forEach( range_1=>{
                    let count_repeats = 0
                    this.ranges.forEach( range_2=>{
                        if( range_1.min === range_2.min ){
                            count_repeats++
                        }
                    })
                    if( count_repeats > 1 ){
                        this.ranges.forEach( range_aux=>{
                            if( range_1.min === range_aux.min ){
                                range_aux.error = true
                            }
                        })
                    } else {
                        range_1.error = false
                    }
                })
            },
            close_drop_ranges(){
               this.show_drop_ranges = false
                if( this.serie_ranges.length > 0 ){
                    console.log(this.serie_ranges)
                    this.ranges = JSON.parse(JSON.stringify(this.serie_ranges))
                } else {
                    this.ranges = [
                       {
                           id : null,
                           min : 5,
                           max : null,
                           free_scort : 0,
                           free_tc : 0,
                           error : false
                       }
                   ]
                }
            },
            add_range(range, key){

                let range_after = this.ranges[key+1]

                let range_new = {
                    id : null,
                    min : ( range.max === null ) ? parseInt( range.min ) + 5 : parseInt( range.max ) + 1,
                    max : ( range_after !== undefined ) ? parseInt( range_after.min  )- 1 : null,
                    free_scort : 0,
                    free_tc : 0,
                    error : false
                }

                this.ranges.splice( (key+1), 0, range_new)
                this.verify_ranges_errors()
            },
            remove_range(range, key){
                this.ranges[key-1].max = range.max
                this.ranges.splice(key, 1)
                this.verify_ranges_errors()
            },
            get_serie_ranges(){

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/ranges'
                )
                    .then((result) => {
                        if( result.data.success ){
                            if( result.data.data.length > 0 ){
                                result.data.data.forEach( r=>{
                                    r.error = false
                                })
                                // this.serie_ranges = result.data.data
                                this.serie_ranges = JSON.parse(JSON.stringify(result.data.data))
                                this.ranges = result.data.data
                                this.show_drop_ranges = false
                            }
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            get_serie_companions(){

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/companions?lang='+this.lang
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.serie_companions = result.data.data
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            get_pay_modes(){

                this.loading = true

                axios.get(
                    baseExternalURL + 'api/pay_modes?lang=' + this.lang
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.pay_modes = result.data.data
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            get_companion_user_types(){

                this.loading = true

                axios.get(
                    baseExternalURL + 'api/usertypes/companions'
                )
                    .then((result) => {
                        if( result.data.success ){
                            result.data.data.forEach( companion =>{
                                companion.check = false
                                companion.quantity = 1
                                companion.pay_mode_id = null
                                companion.user_type_id = companion.id
                                this.serie_companions.forEach( serie_companion=>{
                                    if( companion.id === serie_companion.user_type_id ){
                                        companion.check = true
                                        companion.quantity = serie_companion.quantity
                                        companion.pay_mode_id = serie_companion.pay_mode_id
                                    }
                                } )
                            } )
                            this.companions = result.data.data
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            do_show_drop_categories(){
                this.categories.forEach( (category)=>{
                      category.check = false
                      this.serie_categories.forEach( s_c=>{
                          if( s_c.type_class_id === category.id ){
                              category.check = true
                          }
                      } )
                } )
                this.show_drop_categories = true
            },
            remove_departures(){
                this.loading = true

                let dates_ = []

                this.serie_departures.forEach( departure =>{
                    departure.dates.forEach( day =>{
                        if( day.choose ){
                            dates_.push(day.value)
                        }
                    } )
                } )

                let data = {
                    dates : dates_
                }

                axios.delete(
                    baseExternalURL + 'api/series/departures/'+this.serie_departure_id+'/dates', {data}
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.cancel_delete_departures()
                            this.get_serie_departures()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            cancel_delete_departures(){
                this.serie_departures.forEach( departure=>{
                    departure.dates.forEach( day=>{
                        day.choose = false
                    } )
                } )
                this.show_delete_departures = false
                this.total_delete_departures = 0
            },
            choose_departure_for_delete(day){
                if( !(this.show_delete_departures) ){
                    return
                }
                day.choose=!(day.choose)
                if( day.choose ){
                    this.total_delete_departures++
                } else {
                    this.total_delete_departures--
                }
            },
            get_serie_departures(){

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/departures'
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.total_departures = 0
                            result.data.data.departures.forEach( departure =>{
                                departure.month_string =
                                    ( departure.month <= 9 )
                                        ? '0'+departure.month
                                        : departure.month
                                this.total_departures += departure.dates.length
                                departure.month_name = moment().month(departure.month-1).format("MMMM")
                                departure.dates.forEach( (date_, date_k) =>{
                                    let date_moment = moment(date_)
                                    let day_string =
                                    ( moment(date_).date() <= 9 )
                                        ? '0'+moment(date_).date()
                                        : moment(date_).date()
                                    let date_object = {
                                        choose : false,
                                        label_1 : date_moment.format('dddd').substr(0, 3) + ',',
                                        label_2 : day_string + '/' + departure.month_string,
                                        value : date_
                                    }
                                    departure.dates[date_k] = date_object
                                } )
                            } )
                            this.serie_departures = result.data.data.departures
                            this.serie_departure_id = result.data.data.serie_departure_id
                            this.total_delete_departures = 0
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            get_serie_categories(){

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/categories?lang='+this.lang
                )
                    .then((result) => {
                        if( result.data.success ){
                            result.data.data.forEach( (category, c_k)=>{
                                category.active = (c_k===0)
                            } )
                            this.serie_categories = result.data.data
                            if( this.serie_categories.length>0 ){
                                this.serie_category_id_choose = this.serie_categories[0].id
                                this.get_serie_services()
                            }

                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save_categories(){

                let _type_classes_ids = []

                this.categories.forEach( c=>{
                    if( c.check ){
                        _type_classes_ids.push(c.id)
                    }
                } )

                if( _type_classes_ids.length === 0 ){
                    this.$toast.warning("Por favor seleccione categoría", {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let data = {
                    type_classes_ids : _type_classes_ids
                }

                axios.post(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/categories', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.get_serie_categories()
                            this.show_drop_categories = false
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            save_companions(){

                let _companions = []
                let _errors = 0

                this.companions.forEach( c=>{
                    if( c.check ){
                        _companions.push(c)
                        if( c.pay_mode_id === null || c.quantity < 1 ){
                            _errors++
                        }
                    }
                } )

                if( _companions.length === 0 || _errors > 0 ){
                    this.$toast.warning("Por favor complete información de acompañantes", {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let data = {
                    companions : _companions
                }

                axios.post(
                    baseExternalURL + 'api/series/'+this.serie_id_decode+'/companions', data
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.get_serie_companions()
                            this.show_drop_rates = false
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            toggle_check_all_categories(){
                this.categories.forEach( (category)=>{
                    category.check = !this.check_all_categories
                } )
            },
            search_categories(){

                axios.get(
                    baseExternalURL + 'api/typeclass/selectbox?lang=' + this.lang
                )
                    .then((result) => {
                        if( result.data.success ){
                            let _categories = []
                            result.data.data.forEach( (category)=>{
                                if( category.code.toUpperCase() !== "X" ){
                                    category.check = false
                                    this.serie_categories.forEach( s_c=>{
                                        if( s_c.type_class_id === category.id ){
                                            category.check = true
                                        }
                                    } )
                                    _categories.push(category)
                                }
                            } )
                            this.categories = _categories
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            get_total_notes(){

                axios.get(
                    baseExternalURL + 'api/notes/series/'+this.serie_id_decode+'/total'
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.total_notes = result.data.data
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search(){
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/series/'+this.serie_id_decode
                )
                    .then((result) => {
                        this.loading = false
                        if( result.data.data === null ){
                            window.location = '/error'
                        }
                        this.serie = result.data.data

                        if( this.serie.user_id === this.user_id ){
                            this.type_permission = 0
                        } else {
                            this.serie.users.forEach(ms_u=>{
                                if( ms_u.user_id === this.user_id ){
                                    this.type_permission = ms_u.type_permission
                                }
                            })
                        }

                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            toggleModal(index, _modal, _data) {

                if( this.type_permission===1 ){
                    if( (_modal === 'edit' && _data.serie_selected) || _modal === 'share' ){
                          this.$toast.warning(this.translations.messages.permissions, {
                              position: 'top-right'
                          })
                          return
                    }
                }

                this.index = index
                this.dataModal = _data
                this.modal = 'serie-modal-' + _modal

                let vm = this
                if( this.index === 0 ){
                  setTimeout(function() {
                      vm.$refs.template.load()
                  }, 100)
                } else {
                    let el = document.getElementById('modal_force_'+this.index)
                    setTimeout(function() {
                        el.click()
                    }, 100)
                }
            },
            forceModal(){
                let vm = this
                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            _closeModal() {
                this.modal = ''
                document.getElementsByClassName('modal-backdrop')[0].remove()
                if( document.getElementsByClassName('modal-backdrop fade show')[0] !== undefined ){
                  document.getElementsByClassName('modal-backdrop fade show')[0].remove()
                }
            },
            formatDate (_date, charFrom, charTo, orientation) {
                _date = _date.split(charFrom)
                _date =
                    (orientation)
                        ? _date[2] + charTo + _date[1] + charTo + _date[0]
                        : _date[0] + charTo + _date[1] + charTo + _date[2]
                return _date
            },
            max_width(string_, long_){
                if( string_ !== undefined && string_.length > long_){
                    return string_.substr(0, long_) + '.'
                }
                return string_
            }
        },
        filters : {
            format_date_large(_date) {
                if (_date === undefined) {
                    return;
                }
                let day_string =
                    ( moment(_date).date() <= 9 )
                        ? '0'+moment(_date).date()
                        : moment(_date).date()
                // sab, <span class="date-bold">28 oct</span> 2021
                let _date_concat = moment(_date).format('dddd').substr(0, 3) + ', '
                _date_concat += '<span class="date-bold">' +
                              day_string + ' ' + moment(_date).format('MMMM').substr(0, 3) +
                        '</span> ' + moment(_date).format('YYYY')
                return _date_concat;
            },
            reformatDate(_date) {
                if (_date == undefined) {
                    return;
                }
                _date = moment(_date).format('ddd D MMM YYYY');
                return _date;
            },
            formatHours(_hour) {
                if( _hour === null || _hour === '' ){
                    return _hour
                }
                let hour_split = _hour.split(':')
                let hh = parseInt( hour_split[0] )
                let mm = hour_split[1]
                let _hh
                if (hh >= 12) {
                    hh = (hh!==12) ? (hh-12) : 12
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' PM';
                } else {
                    _hh = (hh <= 9 ) ? '0'+hh : hh
                    return _hh + ':' + mm + ' AM';
                }
            },
            filter_text(value){
                return ( value !== null ) ? value.split('�').join("") : ""
            },
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            },
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }
</script>

<style>
    .cursor-pointer{
        cursor: pointer;
    }
    .icon-disabled{
        color: #b4b4b4;
    }
    .min-w-110{
      min-width: 110px;
    }
  .card-recent{
    background: #F3F9FF !important;
  }
  .card-recent .card-header-master{
    background: #e4f1ff !important;
  }
  .permission_1{
    opacity: 0.5;
  }
  .el_opacity{
      opacity: 0.5;
  }
  .el_error{
    background: #ffd3d3
  }
  .font-size-10{
    font-size: 10px;
  }
  .font-size-13{
    font-size: 13px;
  }
  .without-code{
    font-style: italic;
  }
  .row-warning{
    background-color: #fff8d4 !important;
  }
</style>
