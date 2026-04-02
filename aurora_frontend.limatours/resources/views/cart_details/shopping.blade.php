@extends('layouts.app')
@section('content')
    <div class="shopping">
        <h3><span class="icon-shopping-bag mr-2"></span> Mi carrito de compras <span class="tag-counter">(4 productos)</span></h3>
        <main class="cart-all">
            <div class="basket">
                <div class="services_item">
                    <h4 class="subtitle">(2) Servicios añadidos</h4>
                    <div class="blog-card">
                        <div class="meta">
                            <div class="photo" style="background-image: url(https://storage.googleapis.com/chydlx/codepen/blog-cards/image-1.jpg)"></div>
                        </div>
                        <div class="description">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="subtle"><span class="icon-calendar-confirm mr-1" style="font-size: 1.5rem;"></span> Date jue. 10 dic.</h5>
                                <a class="trash" href="#" ><span class="icon-trash-2"></span></a>
                            </div>
                            <h2 >Audio guide service in the Sacred Valley <span class="cod">[URUX06]</span></h2>
                            <p class="map-pin"><span class="icon-map-pin-in mr-1"></span>Perú, Lima , Lima - <span class="icon-map-pin-out mr-1"></span> Perú, Lima , Lima </p>
                            <h2 >Audio guide service in the Sacred Valley
                                <span class="cod">[URUX06]</span>
                                <span class="ok">OK</span>
                            </h2>
                            <p class="map-pin"><span class="icon-map-pin-in mr-1"></span>Perú, Lima , Lima - <span class="icon-map-pin-out mr-1"></span> Perú, Lima , Lima </p>

                            <div class="multi-services">
                                <div class="d-block" v-if='toggle'>
                                    <span class="text-muted mr-1">[CUS8FE]</span>Lunch at La Feria Restaurant
                                    <button class="btn-multi ml-2" @click='collapsed=!collapsed'><span class="fa fa-angle-right" :class="{'rotate-90': !collapsed}"></span></button>
                                    <button class="btn-multi ml-3" @click='toggle = !toggle'><span class="icon-repeat"></span></button>
                                    <button class="btn-multi ml-2"><span class="icon-trash-2"></span></button>
                                </div>
                                <div class="d-block mb-2" v-else>
                                    <div class="d-flex justify-content-between">
                                        <label class="ml-2 mt-2 text-muted">Seleccionar el servicio a reemplazar:</label>
                                        <div>
                                            <button class="btn-multi ml-4"><span class="icon-save"></span></button>
                                            <button class="btn-multi ml-2" @click='toggle = !toggle'><span class="icon-x"></span></button>
                                        </div>
                                    </div>

                                    <v-select :options="['[LIMXML] Half-day Colonial Lima tour: Casa de Aliaga, Santo Domingo and San Marcos Manor House','[CUS8FE] Lunch at La Feria Restaurant']"
                                              placeholder="Seleccionar servicio"
                                              class="form-control ml-2"/>
                                </div>
                                <transition name="fade">
                                    <div class="collapse-body" v-if="!collapsed">
                                        <p class="mt-3">Lorem ipsum dolor sit amet consectetur adipisicing elit. Laborum repellat deleniti in nam rerum at nesciunt libero ut ad aut! Dolores natus aspernatur recusandae ab, inventore id enim hic nam!</p>
                                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laborum repellat deleniti in nam rerum at nesciunt libero ut ad aut! Dolores natus aspernatur recusandae ab, inventore id enim hic nam!</p>
                                    </div>
                                </transition>
                            </div>

                            <p class="mt-3 mr-5"><span class="icon-add-supplement"></span>Suplementos: Desayuno, gaseosa, entrada</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <label for="staticEmail" class="col-form-label"><span class="icon-clock font-clock"></span></label>
                                    <div class="">
                                        <vue-timepicker ordersname="reservation_time_control" v-model="reservation_time_control" format="HH:mm"></vue-timepicker>
                                    </div>
                                </div>
                                <span class="mr-5"><span class="icon-users"></span> 2 adultos</span>
                                <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>127</span>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-between">
                                    <div class="read-more">
                                        <a href="#note" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample2">
                                            <span class="icon-plus-circle mr-2"></span>Añadir nota</a>
                                    </div>
                                    <div class="read-more">
                                        <a href="#supplements" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample2">
                                            <span class="icon-plus-circle mr-2"></span>Añadir suplementos</a>
                                    </div>
                                </div>
                                <div class="col-12">

                                    <div class="collapse" id="note">
                                        <textarea class="textarea-notas" name="" id="" cols="5" rows="5"></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="collapse" id="supplements">
                                        <div class="card card-body">
                                            <form>
                                                <div class="form-row align-items-center">
                                                    <div class="col-4">

                                                        <label><input type="checkbox"
                                                                      name="optcheckbox"
                                                                      v-model="test">  Suplemento 1</label>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                    </div>
                                                    <div class="col-3 date">
                                                        <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                    </div>
                                                    <div class="col-3 pax">
                                                        <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                    </div>

                                                </div>
                                                <div class="form-row align-items-center">
                                                    <div class="col-4">

                                                        <label><input type="checkbox"
                                                                      name="optcheckbox"
                                                                      v-model="test">  Suplemento 1</label>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                    </div>
                                                    <div class="col-3 date">
                                                        <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                    </div>
                                                    <div class="col-3 pax">
                                                        <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                    </div>
                                                </div>
                                                <div class="form-row align-items-center">
                                                    <div class="col-4">

                                                        <label><input type="checkbox"
                                                                      name="optcheckbox"
                                                                      v-model="test">  Suplemento 1</label>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                    </div>
                                                    <div class="col-3 date">
                                                        <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                    </div>
                                                    <div class="col-3 pax">
                                                        <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="hoteles_item">
                    <h4 class="subtitle">(2) Hoteles añadidos</h4>
                    <div class="blog-card">
                        <div class="meta">
                            <div class="photo" style="background-image: url(https://images.unsplash.com/photo-1530629013299-6cb10d168419?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=335&h=200&q=80)"></div>
                            <ul class="details">
                            </ul>
                        </div>
                        <div class="description">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="subtle d-flex align-items-start"> <span class="icon-calendar-confirm mr-1" style="font-size: 1.5rem;"></span> mié. 13 ene. <span class="icon-arrow-right" ></span> dom. 17 ene. </h5>
                                <div class="read-more ml-5">
                                    <a href="#"><span class="icon-plus-circle mr-2"></span>Ver alternativas</a>
                                </div>
                            </div>
                            <h2 class="d-flex align-items-center">
                                Hotel La Xalca
                                <div class="star">
                                    <img src="https://image.flaticon.com/icons/svg/291/291205.svg" alt="">
                                    <img src="https://image.flaticon.com/icons/svg/291/291205.svg" alt="">
                                    <img src="https://image.flaticon.com/icons/svg/291/291205.svg" alt="">
                                    <img src="https://image.flaticon.com/icons/svg/291/291205.svg" alt="">
                                </div>
                            </h2>
                            <p>
                            <p class="mt-3 mr-5"><span class="icon-add-supplement"></span>Suplementos: Desayuno, gaseosa, entrada</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class=""><span class="icon-bed-double mr-2"></span>4 Habitaciones</div>
                                <div class=""><span class="icon-users mr-2"></span>8 Personas</div>
                            </div>
                            </p>
                            <div class="mini-card">
                                <div class="text-right"><a class="trash" href="#" ><span class="icon-trash-2"></span></a></div>
                                <h2 style="color: #4a90e2;">TRADITIONAL SIMPLE <span class="text mr-2">Regular Rate FITS Y GROUPS</span><span class="ok">OK</span></h2>
                                <p style="font-size: 1.15rem;">You have until the 06-01-2021 to cancel without penalties, after that date you will pay the amount of $ 213.00</p>

                                <div class="read-more">
                                    <span class="mr-5"><span class="icon-users"></span> 2 adultos</span>
                                    <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>127</span>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="read-more">
                                            <a href="#note" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapsenote">
                                                <span class="icon-plus-circle mr-2"></span>Añadir nota</a>
                                        </div>
                                        <div class="read-more">
                                            <a href="#supplements" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample2">
                                                <span class="icon-plus-circle mr-2"></span>Añadir suplementos</a>
                                        </div>
                                    </div>
                                    <div class="col-12">

                                        <div class="collapse" id="note">
                                            <textarea class="textarea-notas" name="" id="" cols="5" rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="collapse" id="supplements">
                                            <div class="card card-body">
                                                <form>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mini-card">
                                <div class="text-right"><a class="trash" href="#" ><span class="icon-trash-2"></span></a></div>
                                <h2 style="color: #4a90e2;">TRADITIONAL SIMPLE <span class="text mr-2">Regular Rate FITS Y GROUPS</span><span class="rq">RQ</span></h2>
                                <p style="font-size: 1.15rem;">You have until the 06-01-2021 to cancel without penalties, after that date you will pay the amount of $ 213.00</p>
                                <p class="mt-3 mr-5"><span class="icon-add-supplement"></span>Suplementos: Desayuno, gaseosa, entrada</p>

                                <div class="read-more">
                                    <span class="mr-5"><span class="icon-users"></span> 2 adultos</span>
                                    <span class="price_"><span class="icon-dollar-sign1 mr-2"></span>127</span>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-between">
                                        <div class="read-more">
                                            <a href="#note" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapsenote">
                                                <span class="icon-plus-circle mr-2"></span>Añadir nota</a>
                                        </div>
                                        <div class="read-more">
                                            <a href="#supplements" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample2">
                                                <span class="icon-plus-circle mr-2"></span>Añadir suplementos</a>
                                        </div>
                                    </div>
                                    <div class="col-12">

                                        <div class="collapse" id="note">
                                            <textarea class="textarea-notas" name="" id="" cols="5" rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="collapse" id="supplements">
                                            <div class="card card-body">
                                                <form>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-row align-items-center">
                                                        <div class="col-4">

                                                            <label><input type="checkbox"
                                                                          name="optcheckbox"
                                                                          v-model="test">  Suplemento 1</label>
                                                        </div>
                                                        <div class="col-2">
                                                            <input type="number" class="form-control" placeholder="2" style="height: 30px;">
                                                        </div>
                                                        <div class="col-3 date">
                                                            <v-select multiple v-model="selected" :options="['06/01/21','07/01/21','08/01/21']" class="form-control"/>
                                                        </div>
                                                        <div class="col-3 pax">
                                                            <v-select multiple v-model="selected" :options="['Pasajero 1','Pasajero 2','Pasajero 3']" class="form-control"/>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <aside>
                <div class="summary">
                    <div class="card card-cart">
                        <h2 class="title-card">Mis productos</h2>
                        <div>
                            <div class="d-flex justify-content-between">
                                <div class="text-title"><span class="icon-check mr-1"></span>AUDIO GUIDE SERVICE IN THE SACRED VALLEY(2 personas)</div>
                                <div>$ 215.00</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="text-title"><span class="icon-check mr-1"></span>AUDIO GUIDE SERVICE IN THE SACRED VALLEY(2 personas)</div>
                                <div>$ 215.00</div>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between total"><div>TOTAL</div><div> $311.97</div></div>
                        </div>
                        <hr>
                        <div class="mt-5">
                            <h4><strong>Información de File</strong></h4>
                            <form>
                                <div class="form-group row">
                                    <label for="" class="col-5 col-form-label">N° de File</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" style="height: 30px;">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-5 col-form-label">N° de referencia</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" style="height: 30px;">
                                    </div>
                                </div>
                            </form>

                        </div>
                        <div class="mt-5">
                            <div class="personal-data-item">
                                <h4><strong>Datos de los pasajeros</strong></h4>
                                <div class="mb-3 d-flex align-items-center">
                                    <div class="mr-5"><span class="icon-users mr-2"></span><span><b>2 </b> Adultos</span></div>
                                    <div class="mr-5"><span class="icon-smile mr-2"></span><span><b>2 </b> Niños</span></div>
                                    <b-button v-on:click="modalPassengers()" class="btn-lg">
                                        <i class="icon-edit"></i> Datos del pasajero
                                    </b-button>
                                </div>
                            </div>

                        </div>
                        <div class="my-3">
                            <a class="btn btn-primary btn-car"
                               href="/reservations/personal-data">{{ trans('cart_view.label.reserve') }}</a>
                        </div>
                        <div class="d-block text-canceled">
                            Cancelación gratuita hasta el 25 dic. a las 1:00 PM
                            Pasado este plazo, cancela antes del 26 dic. a las 1:00 PM y consigue un reembolso completo, menos la primera noche y la comisión de servicio.
                            <a href="/">Más información</a>
                        </div>

                    </div>

                </div>
            </aside>
        </main>
    </div>
    <!------- Modal Datos pasajeros ------->
    <modal-passengers ref="modal_passengers"></modal-passengers>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                toggle: true,
                collapsed: true,
                reservation_time_control: {
                    HH: '00',
                    mm: '00',
                },
                reservation_time: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                    defaultDate: moment().format(),
                    minDate: moment().format(),
                },
                test:'',
                selected:'',
            },
            mounted(){
                console.log(this.toggle)
            },
            methods: {
                modalPassengers: function () {
                    this.$refs.modal_passengers.modalPassengers()
                },
            }
        })
    </script>

@endsection
