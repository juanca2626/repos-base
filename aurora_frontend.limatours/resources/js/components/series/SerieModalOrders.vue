<template>
    <div class="modal fade modal-general content-ordes" id="modal_ordes">
        <div class=" content-ordes__modal"  >
            <div class="modal-content-orders  modal-content-orders--2 modal-dialog modal-dialog__custom" role="document">
                <div class="modal-content modal-content__container">
                    <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                    <div class="modal-body container-content">
                        <!-- PRIMERA PANTALLA completo maqueta-->
                        <div class="">
                            <h2 class="container-content__title">Pedidos</h2>
                            <div class="d-flex mt-2 mb-3">
                                <h5 class="title-h5">ISLANDER IL62007 (PERU ESCAPE)</h5>
                                <span class="span-modal">de Lima Tours</span>
                            </div>
                            <div>
                                <div class="container-content__details">

                                    <div class="details-left">
                                        <p class="container-content__detail">Selecciona una fecha para validar el estado de los pedidos.</p>
                                    </div>
                                    <div class="details-right details-right-ico">
                                        <div class="details-right__item">
                                            <span class="icon-calendar"></span>
                                            <span class="item-number">27</span>
                                        </div>
                                        <div class="details-right__item">
                                            <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span>
                                            <span class="item-number">19</span>
                                        </div>
                                        <div class="details-right__item">
                                            <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span>
                                            <span class="item-number">3</span>
                                        </div>
                                        <div class="details-right__item">
                                            <span class="icon-thumbs-down" style="color: rgb(206, 59, 77);"></span>
                                            <span class="item-number">5</span>
                                        </div>
                                    </div>
                                </div>
                                <ul class="container-content__list">
                                    <li class="list-item">
                                        <span class="list-item__month">Octubre / 2021</span>
                                    </li>
                                    <li class="list-item">
                                        <p class="list-item__group">sab, <span class="group-bold">28/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">dom, <span class="group-bold">29/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">lun, <span class="group-bold">30/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mar, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>

                                    </li>
                                </ul>
                                <ul class="container-content__list">
                                    <li class="list-item">
                                        <span class="list-item__month">Octubre / 2021</span>
                                    </li>
                                    <li class="list-item">
                                        <p class="list-item__group">sab, <span class="group-bold">28/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">dom, <span class="group-bold">29/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">lun, <span class="group-bold">30/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mar, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                    </li>
                                </ul>
                                <ul class="container-content__list">
                                    <li class="list-item">
                                        <span class="list-item__month">Octubre / 2021</span>
                                    </li>
                                    <li class="list-item">
                                        <p class="list-item__group">sab, <span class="group-bold">28/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">dom, <span class="group-bold">29/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">lun, <span class="group-bold">30/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mar, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                    </li>
                                    <li class="list-item">
                                        <p class="list-item__group">sab, <span class="group-bold">28/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">dom, <span class="group-bold">29/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">lun, <span class="group-bold">30/10</span> <span class="icon-thumbs-down" style="color: rgb(206, 59, 77);"></span></p>
                                        <p class="list-item__group">mar, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                    </li>
                                    <li class="list-item">
                                        <p class="list-item__group">sab, <span class="group-bold">28/10</span> <span class="icon-thumbs-down" style="color: rgb(206, 59, 77);"></span></p>
                                        <p class="list-item__group">dom, <span class="group-bold">29/10</span> <span class="icon-thumbs-down" style="color: rgb(206, 59, 77);"></span></p>
                                        <p class="list-item__group">lun, <span class="group-bold">30/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">mar, <span class="group-bold">31/10</span> <span class="icon-thumbs-up" style="color: rgb(4, 181, 170);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                        <p class="list-item__group">mie, <span class="group-bold">31/10</span> <span class="icon-inbox" style="color: rgb(234, 147, 45);"></span></p>
                                    </li>
                                </ul>
                            </div>

                        </div>
                        <!-- SEGUNDA PANTALLA -->
                        <!-- <div class="container-content container-content--2">
                                              <a class="container-content__close" data-dismiss="modal">Cerrar <span class="close-action">X</span></a>
                                              <h2 class="container-content__title">Pedidos</h2>
                                              <div class="container-content__destination-d">
                                                  <span class="container-content__destination">Grupo para Cusco <span class="destination-agency">(Lima Tours)</span></span>
                                                  <a href="#" class="btn-section">volver a lista</a>
                                              </div>

                                              <div class="container-content__datepicker">
                                                  <input class="datepicker-start" type="text" placeholder="sab, 28/10/2021">
                                                  <input class="datepicker-end" type="text" placeholder="Turista Superior">
                                                  <span class="container-content__txt">TODO CONFIRMADO</span>
                                              </div>
                                              <div class="content-tab--header">
                                                  <div class="content-tab--header__menu">
                                                      <ul class="menu-client menu-client nav nav-tabs">
                                                          <li class="menu-client__list">
                                                              <a class="list-item list-item--tourist nav-link active" data-toggle="tab" href="#tourist2" style="pointer-events: none">Hotel</a>
                                                          </li>
                                                          <li class="menu-client__list">
                                                              <a class="list-item list-item--superior-tourist nav-link" data-toggle="tab" href="#superior_tourist2" style="pointer-events: none">Servicios</a>
                                                          </li>
                                                          <li class="menu-client__list">
                                                              <a class="list-item list-item--first nav-link" data-toggle="tab" href="#first2" style="pointer-events: none">Trenes</a>
                                                          </li>
                                                      </ul>
                                                  </div>
                                              </div>
                                              <div class="content-tab--body">
                                                  <div class="content-tab--body__container">
                                                      <div id="tourist2" class="container-content container-content--modified2 tab-pane active">
                                                          <div class="container-content__body container-content__body--draggable list-group" id="accordionTourist2">
                                                              <div class="body-group">
                                                                  <div class="body-item list-group-item">
                                                                      <div class="body-item__description">
                                                                          <div class="description-content">
                                                                              <div class="description-content__box-left">
                                                                                  <span class="txt">CUZ321</span>
                                                                                  <span class="destination">Inkaterra Valle Sagrado (15)<span class="destination__detail">(Principal)</span></span>
                                                                              </div>
                                                                              <div class="description-content__box-right">
                                                                                  <span class="date">sab, <span class="date__month">28 oct 2021</span><span class="date__day"> / 2N</span><i class="date__ico icon-resource--alternative icon-serie-confirmado"></i></span>
                                                                              </div>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="detail-collapse collapse">
                                                                      <li class="body-item body-item--collapse list-group-item">
                                                                          <div class="body-item__collapse">
                                                                              <ul class="collapse-list">
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                              </ul>
                                                                          </div>
                                                                      </li>
                                                                  </div>
                                                              </div>
                                                              <div class="body-group">
                                                                  <div class="body-item list-group-item">
                                                                      <div class="body-item__description">
                                                                          <div class="description-content">
                                                                              <div class="description-content__box-left">
                                                                                  <span class="txt">CUZ321</span>
                                                                                  <span class="destination">Inkaterra Valle Sagrado (15)<span class="destination__detail">(Principal)</span></span>
                                                                              </div>
                                                                              <div class="description-content__box-right">
                                                                                  <span class="date">sab, <span class="date__month">28 oct 2021</span><span class="date__day"> / 2N</span><i class="date__ico icon-resource--alternative icon-serie-confirmado"></i></span>
                                                                              </div>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="detail-collapse collapse">
                                                                      <li class="body-item body-item--collapse list-group-item">
                                                                          <div class="body-item__collapse">
                                                                              <ul class="collapse-list">
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                              </ul>
                                                                          </div>
                                                                      </li>
                                                                  </div>
                                                              </div>
                                                              <div class="body-group">
                                                                  <div class="body-item list-group-item">
                                                                      <div class="body-item__description">
                                                                          <div class="description-content">
                                                                              <div class="description-content__box-left">
                                                                                  <span class="txt">CUZ321</span>
                                                                                  <span class="destination">Inkaterra Valle Sagrado (15)<span class="destination__detail">(Principal)</span></span>
                                                                              </div>
                                                                              <div class="description-content__box-right">
                                                                                  <span class="date">sab, <span class="date__month">28 oct 2021</span><span class="date__day"> / 2N</span><i class="date__ico icon-resource--alternative icon-serie-confirmado"></i></span>
                                                                              </div>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                                  <div class="detail-collapse collapse">
                                                                      <li class="body-item body-item--collapse list-group-item">
                                                                          <div class="body-item__collapse">
                                                                              <ul class="collapse-list">
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                                  <li class="collapse-list__item">
                                                                                      <span class="item-sgl">SGL</span>
                                                                                      <span class="item-standard">Standard</span>
                                                                                      <input class="text" type="text" placeholder="2">
                                                                                      <div class="content-actions">
                                                                                          <span class="content-add">+</span>
                                                                                          <span class="content-delete">-</span>
                                                                                      </div>
                                                                                      <span class="confirmed">Confirmado</span>
                                                                                  </li>
                                                                              </ul>
                                                                          </div>
                                                                      </li>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </div>
                                                      <div id="superior_tourist2" class="container-content tab-pane fade">
                                                      </div>
                                                      <div id="first2" class="container-content tab-pane fade">
                                                      </div>
                                                  </div>
                                              </div>
                                          </div> -->
                        <!--                    <div class=" modal fade"  tabindex="-1" role="dialog" aria-labelledby="modal_ordesLabel" aria-hidden="true">-->
                        <!--                        <div class="" role="document">-->
                        <!--                            <div class="">-->

                        <!--                            </div>-->
                        <!--                        </div>-->
                        <!--                    </div>-->


                    </div>
                </div>
            </div>

        </div>
    </div>

</template>
<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                lang: '',
                loading: false,
                modal_size: '',
                modal: '',
                dataModal: {},
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.translations = this.data.translations

        },
        computed: {

        },
        methods: {
            load(){

            },
            close_modal(){
                this.$parent._closeModal()
            },
            toggle_content(){
            }
        },
        filters:{
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            }
        }
    }
</script>
<style>
    .modal-size-small{
        width: 640px !important;
    }
</style>
