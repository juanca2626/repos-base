<div id="modal-detail-servicios" class="modal fade show modal-servicios" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true" ref="vuemodal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button id="modal_detail_close" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="border-radius: 10px!important;">
                <div class="service-modal-container">                                    
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="modal-title" id="summary">@{{ modal.title }}</h2>
                            <hr>
                            <div class="service-seleccion">
                                <p style="white-space: pre-wrap;" v-html="modal.details"></p>
                            </div>
                        </div>
                    </div>                         
                </div>
            </div>
        </div>
    </div>
</div>
