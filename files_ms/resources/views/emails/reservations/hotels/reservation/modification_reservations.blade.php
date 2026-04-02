<div style="display: flex; align-items: center;">
    <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/calendar.png" alt="#"
        width="25" height="18" style="display:block; padding-right: 0.2rem"
        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
        mc:allowtext />
    <p
        style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
        Fechas
    </p>
</div>

<div style="padding: 0 0.3rem;">
    <p
        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D">
        <strong style="color: #3D3D3D; font-size: 14px">Check in:</strong>
        <span style="padding-right: 0.5rem"> {{ \Carbon\Carbon::parse($date_in)->format('d/m/Y') }} </span>
        <strong style="color: #EB5757; padding-right: 0.5rem">|</strong>
        {{ $start_time }}
    </p>
    <p
        style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D">
        <strong style="color: #3D3D3D; font-size: 14px;">Check out:</strong>
        <span style="padding-right: 0.5rem"> {{ \Carbon\Carbon::parse($date_out)->format('d/m/Y') }}</span>
        <strong style="color: #EB5757; padding-right: 0.5rem">|</strong>
        {{ $departure_time }}
    </p>
    @php
        $totalRooms = collect($rooms)->sum('total_rooms');
    @endphp
    <p
        style="font-weight: 500; text-align: left; margin: 0rem 0.5rem 0rem auto; color: #3D3D3D">
        <strong style="color: #3D3D3D; font-size: 14px;">Cantidad:</strong>
        {{ $nights }} {{ $nights > 1 ? 'noches' : 'noche' }}
        <strong style="color: #EB5757; padding-right: 0.5rem">|</strong> {{ $totalRooms }}
        {{ $totalRooms > 1 ? 'habitaciones' : 'habitación' }}
    </p>
</div>

<div style="display: flex; padding: 1.5rem 0rem; align-items: center;">
    <img src="https://res.cloudinary.com/litodti/image/upload/v1744314216/aurora/iconos/bed.png" alt="#"
        width="24" height="20" style="display:block; padding-right: 0.2rem;"
        mc:label="header_image" mc:edit="module_image" mc:allowdesigner
        mc:allowtext />
    <p
        style="font-weight: 600; text-align: left; color: #EB5757; margin:0;">
        Detalles para habitaciones</p>
</div>
@foreach($rooms as $k => $resHotRoom)
    <div style="padding: 0rem 0.3rem 2.5rem;">
        <p
            style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
            <strong style="color: #3D3D3D; font-size: 14px;">
                Tipo de habitación:
            </strong> {{ $resHotRoom['room_name'] }}
        </p>
        <p
            style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
            <strong style="color: #3D3D3D; font-size: 14px;">
                Cantidad:
            </strong> {{ $resHotRoom['total_rooms'] }} {{ $resHotRoom['total_rooms'] > 1 ? 'habitaciones':'habitación' }}
        </p>
        <p
            style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D;">
            <strong style="color: #3D3D3D; font-size: 14px;">
                Ocupantes:</strong> {{ $resHotRoom['occupants'] }} {{ $resHotRoom['occupants'] > 1 ? 'pasajeros' : 'pasajero' }}
        </p>

        <p
            style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
            <strong style="color: #3D3D3D; font-size: 14px;">Plan tarifario:
            </strong>
            {{ $resHotRoom['rate_plan_name'] }} | {{ $resHotRoom['rate_plan_code'] }}
        </p>


        <div style="display: flex; align-items: center; margin-bottom: 0.3rem;">
            <img src="https://res.cloudinary.com/litodti/image/upload/v1744302640/aurora/iconos/icon-dolar.png" alt="#"
                width="10" height="18"
                style="display:block; padding-right: 0.2rem;"
                mc:label="header_image" mc:edit="module_image" mc:allowdesigner
                mc:allowtext />
            <p
                style="font-weight: 600; font-size: 14px; text-align: left; color: #EB5757; margin:0;">
                Total de tarifa:
                <strong style="color: #3D3D3D">
                    @if($type == 'cost')
                        {{ $resHotRoom['amount_cost'] }}
                    @else
                        {{ $resHotRoom['amount_sale'] }}
                    @endif
                </strong>
            </p>

        </div>


        @if($resHotRoom['channel_id'] == 6 and $resHotRoom['channel_reservation_code_master'] )
            <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
                <strong style="color: #3D3D3D; font-size: 14px;">Código Confirmación Channel: </strong>
                {{ $resHotRoom['channel_reservation_code_master'] }}
            </p>
        @endif

        <p style="font-weight: 500; text-align: left; margin-top: 0rem; margin-bottom: 0.5rem; color: #3D3D3D; ">
        <strong style="padding-right:0.5rem; color: #3D3D3D; font-size: 14px; float: left">
            Status de la reserva:
            </strong>
        </p>
        @if($resHotRoom['confirmation_status'])
            <span style="float: left; display: flex;background: #06C270;border-radius: .5rem;width: max-content;padding: 0.4rem;color: white;font-weight: 700;font-size: 14px;">
            <img src="https://res.cloudinary.com/litodti/image/upload/v1744314217/aurora/iconos/check-list.png" alt="#" width="20" style="display:block; padding-right: 0.2rem; align-items: center;"
                mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                Confirmada
            </span>
        @else
            <span style="float: left; display: flex;background: #FFCC00;border-radius: .5rem;width: max-content;padding: 0.4rem;color: white;font-weight: 700;font-size: 14px;">
            <img src="https://res.cloudinary.com/litodti/image/upload/v1744314217/aurora/iconos/warning.png" alt="#" width="20" style="display:block; padding-right: 0.2rem; align-items: center;"
                mc:label="header_image" mc:edit="module_image" mc:allowdesigner mc:allowtext/>
                On request
            </span>
        @endif

        <p style="clear: both"></p>
    </div>
@endforeach