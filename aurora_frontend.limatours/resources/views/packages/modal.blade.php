<!-- Modal -->
<div id="modalExtension" class="modal fade show modal-extensiones" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true" v-if="package_selected[1] !== null" ref="vuemodal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <h2 class="modal-title" id="myLargeModalLabel">{{trans('package.label.add_extensions')}}</h2>
                <div class="intro">{{trans('package.label.you_have_selected_the_program')}}, <span class="color">{{trans('package.label.would_you_like_to_include_an_extension')}}</span>
                </div>
                <div class="continue">
                    <span>{{trans('package.label.no_thanks_prefer')}}...</span>
                    <button class="btn btn-primary"
                            @click="savePackagesSelected()">{{trans('package.label.continue')}}</button>
                </div>
                <div class="ext-seleccion">
                    <div class="block block-extension" id="ext1"
                         :data-extension="(package_selected[0] !== null) ? package_selected[0].id : 0">
                        <div class="extension" v-if="package_selected[0] == null"></div>
                        <div class="programa" v-if="package_selected[0] != null" @click="dragClick()">
                            <div class="programa-detalle">
                                <div class="bloque-info">
                                    <div class="duracion">@{{
                                        package_selected[0].nights + 1 }}D/@{{
                                        package_selected[0].nights }}N
                                    </div>
                                    <div class="nombre">@{{ package_selected[0].translations[0].name }}</div>
                                    <div class="ruta">@{{ package_selected[0].destinations }}</div>
                                    <div class="clasificacion">
                                        <!-- Incluir para Tooltip: data-toggle="tooltip" title="NOMBRE" -->
                                        <span class="tipo" style="--tipo-color:#4A90E2">@{{ package_selected[0].tag.translations[0].value }}</span>
                                        <span class="tipo" style="--tipo-color:#fcf91b"
                                              v-show="package_selected[0].recommended >0">{{trans('package.label.recommended')}}</span>
                                    </div>
                                </div>
                                <div class="bloque-precio">
                                    <div class="precio">
                                        <small>{{trans('package.label.from')}}</small>
                                        <span class="currency">$</span>
                                        <span class="valor">@{{ roundLito(package_selected[0].price) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="programa-foto">
                                <img :src="package_selected[0].galleries[0]" alt="Image Package"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     v-if="package_selected[0].galleries.length > 0">
                                <img :src="package_selected[0].image_link"
                                     v-else-if="package_selected[0].image_link && package_selected[0].image_link != '' && package_selected[0].image_link != null">
                                <img
                                    src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                    alt="Image Package" v-else>
                            </div>
                        </div>
                    </div>
                    <div class="block block-programa">
                        <div class="programa" v-if="package_selected[1]">
                            <div class="programa-detalle">
                                <div class="bloque-info">
                                    <div class="duracion">@{{ package_selected[1].nights + 1 }}D/@{{
                                        package_selected[1].nights }}N
                                    </div>
                                    <div class="nombre">@{{ package_selected[1].translations[0].name }}</div>
                                    <div class="ruta">@{{ package_selected[1].destinations }}</div>
                                    <div class="clasificacion">
                                        <!-- Incluir para Tooltip: data-toggle="tooltip" title="NOMBRE" -->
                                        <span class="tipo" style="--tipo-color:#04B5AA">@{{ package_selected[1].tag.translations[0].value }}</span>
                                        <span class="tipo" style="--tipo-color:#fcf91b"
                                              v-show="package_selected[1].recommended >0">{{trans('package.label.recommended')}}</span>
                                    </div>
                                </div>
                                <div class="bloque-precio">
                                    <div class="precio">
                                        <small>{{trans('package.label.from')}}</small>
                                        <span class="currency">$</span>
                                        <span class="valor">@{{ roundLito(package_selected[1].price) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="programa-foto">
                                <img :src="package_selected[1].galleries[0]" alt="Image Package"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     v-if="package_selected[1].galleries.length > 0">
                                <img :src="package_selected[1].image_link"
                                     v-else-if="package_selected[1].image_link && package_selected[1].image_link != '' && package_selected[1].image_link != null">
                                <img src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                    alt="Image Package" v-else>
                            </div>
                        </div>
                    </div>
                    <div class="block block-extension" id="ext2"
                         :data-extension="(package_selected[2] !== null) ? package_selected[2].id : 0">
                        <div class="extension" v-if="package_selected[2] == null"></div>
                        <div class="programa" v-if="package_selected[2] !== null" @click="dragClick()">
                            <div class="programa-detalle">
                                <div class="bloque-info">
                                    <div class="duracion" v-if="package_selected[2] != null">@{{
                                        package_selected[2].nights + 1 }}D/@{{
                                        package_selected[2].nights }}N
                                    </div>
                                    <div class="nombre">@{{ package_selected[2].translations[0].name }}</div>
                                    <div class="ruta">Lima, Cusco, Valle Sagrado y Machu Picchu</div>
                                    <div class="clasificacion">
                                        <!-- Incluir para Tooltip: data-toggle="tooltip" title="NOMBRE" -->
                                        <span class="tipo" style="--tipo-color:#4A90E2">@{{ package_selected[2].tag.translations[0].value }}</span>
                                        <span class="tipo" style="--tipo-color:#fcf91b"
                                              v-show="package_selected[2].recommended >0">{{trans('package.label.recommended')}}</span>
                                    </div>
                                </div>
                                <div class="bloque-precio">
                                    <div class="precio">
                                        <small>{{trans('package.label.from')}}</small>
                                        <span class="currency">$</span>
                                        <span class="valor">@{{ roundLito(package_selected[2].price) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="programa-foto">
                                <img :src="package_selected[2].galleries[0]" alt="Image Package"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     v-if="package_selected[2].galleries.length > 0">
                                <img :src="package_selected[2].image_link"
                                     v-else-if="package_selected[2].image_link && package_selected[2].image_link != '' && package_selected[2].image_link != null">
                                <img
                                    src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                    alt="Image Package" v-else>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4>{{trans('package.label.drag_the_extension_you_want_to_include')}}.</h4>
                    <div class="ext-lista owl-carousel">
                        <div :class="{'extension':true, 'item':true}"
                             :data-extension="package.id" draggable="true"
                             v-for="(package,index_package) in extensions">
                            <span :class="{'extension':true, 'item':true, 'recomendado':package.recommended > 0}" v-if="package.recommended > 0">
                                {{trans('package.label.recommended')}}
                            </span>
                            <div class="programa">
                                <div class="programa-detalle">
                                    <div class="bloque-info">
                                        <div class="duracion">@{{ package.nights + 1 }}D/@{{ package.nights }}N</div>
                                        <div class="nombre" v-html="package.translations[0].name"></div>
                                        <div class="ruta">@{{ package.destinations }}</div>
                                        <span class="tipo" style="--tipo-color:#04B5AA">@{{ package.tag.translations[0].value }}</span>
                                        <span class="tipo" style="--tipo-color:#fcf91b" v-if="package.recommended >0">{{trans('package.label.recommended')}}</span>
                                    </div>
                                    <div class="bloque-precio">
                                        <div class="precio">
                                            <small>{{trans('package.label.from')}}</small>
                                            <span class="currency">$</span>
                                            <span class="valor">@{{ roundLito(package.price) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="programa-foto">
                                    <img :src="package.galleries[0]" alt="Image Package"
                                         onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                         v-if="package.galleries.length > 0">
                                    <img :src="package.image_link" alt="Image Package"
                                         v-else-if="package.image_link && package.image_link != '' && package.image_link != null">
                                    <img
                                        src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                        alt="Image Package" v-else>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

