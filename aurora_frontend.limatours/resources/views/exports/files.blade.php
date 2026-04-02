<style>
    
    @font-face {
      font-family: 'Courier New';
      font-style: normal;
      font-weight: normal;
      src: url({{ storage_path("fonts/cour.ttf") }}) format("truetype");
    }

    body {
        font-family: 'Courier New'; 
        font-size: 12px
    }

    table {
        /* margin-left: 20px;
        margin-right: 20px; */
        margin-top: 15px;
        margin-bottom: 15px;
        width: 100%;
        border-collapse: collapse; 
    }

    table th,td {      
        border-style: dashed;
        font-family: 'Courier New'; 
        vertical-align: top;
        padding: 10px!important;
        border-bottom: 0px solid!important;
    }

    .header{
        text-align: center; 
    }

    .page-break {
        page-break-after: always;
    }

</style>
@php
   $document = 'invoice';
   $tabla = $detalle['report'][0];
@endphp

@include('exports.file_body')


@php
   $document = 'credit_note';
   $tabla = $detalle['notes']['notacredito']
@endphp

@if(count($tabla)>0)
<div class="page-break"></div>
@include('exports.file_body')
@endif

@php
   $document = 'debit_note';
   $tabla = $detalle['notes']['notadebito']
@endphp

@if(count($tabla)>0)
<div class="page-break"></div>
@include('exports.file_body')
@endif
