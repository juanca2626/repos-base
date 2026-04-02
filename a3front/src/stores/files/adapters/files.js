export const createFilesAdapter = (field) => createFileAdapter(field);

export const createFileAdapter = (field) => ({
  id: field.id,
  clientId: field.client_id,
  reservationId: field.reservation_id,
  orderNumber: field.order_number,
  fileNumber: field.file_number,
  revisionStages: field.revision_stages,
  opeAssignStages: field.ope_assign_stages,
  executiveCode: field.executive_code,
  executiveName: field.executive_name,
  clientCode: field.client_code,
  clientName: field.client_name,
  categories: field.categories,
  description: field.description,
  dateIn: field.date_in,
  dateOut: field.date_out,
  adults: field.adults,
  children: field.children,
  infants: field.infants,
  observation: field.observation,
  haveInvoice: field.have_invoice,
  status: field.status.toLowerCase(),
  lang: field.lang.toLowerCase(),
  statusReason: field.status_reason,
  statusReasonId: field.status_reason_id,
  createdAt: field.created_at,
  deadline: field.deadline,
  vips: field.vips.map(({ file_id, vip_id, vip, id }) => ({
    fileId: file_id,
    vipId: vip_id,
    vipName: vip.name,
    id,
  })),
  amountSale: field.total_amount,
  amountCost: field.total_cost_amount,
  passengerChanges: field.passenger_changes,
  profitability: field.profitability,
  markupClient: field.markup_client,
  services: field.services,
  // custom fields
  paxs: Number(field.adults) + Number(field.children) + Number(field.infants),
  isVip: field.vips.length > 0,
  generateStatement: field.generate_statement,
  suggested_accommodation_sgl: field.suggested_accommodation_sgl,
  suggested_accommodation_dbl: field.suggested_accommodation_dbl,
  suggested_accommodation_tpl: field.suggested_accommodation_tpl,
  viewRateProtected: field.view_rate_protected,
  protectedRate: field.protected_rate,
  statement: field.statement,
  origin: field.origin,
  stela_processing: field.stela_processing,
  showMasterServices: field.show_master_services,
  processing: field.processing,
});

export const normalizeItineries = (itineraries, loading = true) => {
  itineraries.map((s) => {
    s.validationOpe = false;
    s.isNew = s.isNew ?? false;
    s.isUpdated = s.isUpdated ?? false;
    s.isError = s.isError ?? false;
    s.errorDetail = '';
    s.isCompleted = !s.isCompleted ? false : s.isCompleted;
    s.isLoading = loading;
    s.flights_completed = false;
    if (s.entity === 'flight') {
      let f_registered = 0;
      let total_paxs = 0;
      s.flights.forEach((f) => {
        f.form_on = false;
        f.departure_time =
          f.departure_time !== null && f.departure_time !== ''
            ? f.departure_time.substring(0, 5)
            : '';
        f.arrival_time =
          f.arrival_time !== null && f.arrival_time !== '' ? f.arrival_time.substring(0, 5) : '';
        if (
          f.airline_code !== null &&
          f.airline_code !== '' &&
          f.airline_number !== null &&
          f.airline_number !== '' &&
          f.departure_time !== null &&
          f.departure_time !== '' &&
          f.accommodations.length > 0
        ) {
          f_registered++;
          total_paxs += f.accommodations.length;
        }
      });
      if (
        s.flights.length > 0 &&
        f_registered >= s.flights.length &&
        parseInt(s.adults) + parseInt(s.children) === total_paxs
      ) {
        s.flights_completed = true;
      }

      s.permitted_paxs = parseInt(s.adults) + parseInt(s.children);
      s.is_valid = parseInt(s.adults) + parseInt(s.children) === total_paxs;
      s.total_paxs = total_paxs;
    }

    s.flag_show = localStorage.getItem(`itinerary_${s.id}_flag_show`) === 'true';

    const services = Array.isArray(s?.services) ? s.services : [];
    services.map((service) => {
      service.flag_show =
        localStorage.getItem(`itinerary_service_${service.id}_flag_show`) === 'true';
      return service;
    });

    const rooms = Array.isArray(s?.rooms) ? s.rooms : [];
    rooms.map((room) => {
      room.flag_show = localStorage.getItem(`itinerary_room_${room.id}_flag_show`) ?? false;
      const units = Array.isArray(room?.units) ? room.units : [];
      units.map((unit) => {
        unit.flag_show =
          localStorage.getItem(`itinerary_room_unit_${unit.id}_flag_show`) === 'true';
        return unit;
      });

      return room;
    });

    /*
    const items_auto_order = items.filter(
      (item) => item.date == s.date_in && item.code == s.object_code
    );

    items.push({
      date: s.date_in,
      code: s.object_code,
    });

    s.auto_order = items_auto_order.length + 1;
    */

    return createFileServiceAdapter(s);
  });

  return itineraries;
};

export const createFileServiceAdapter = (field) => field;

export const createFilePassengerAdapter = (field) => ({
  id: field.id,
  city_iso: field.city_iso,
  country_iso: field.country_iso,
  date_birth: field.date_birth ? field.date_birth : '',
  dietary_restrictions: field.dietary_restrictions,
  doctype_iso: field.doctype_iso,
  document_number: field.document_number,
  document_type_id: field.document_type_id,
  email: field.email,
  file_id: field.file_id,
  frequent: field.frequent,
  genre: field.genre,
  medical_restrictions: field.medical_restrictions,
  name: field.name,
  given_name: field.name,
  surname: field.surnames,
  notes: field.notes,
  order_number: field.order_number,
  phone: field.phone,
  phone_code: field.phone_code.toString(),
  sequence_number: field.sequence_number,
  room_type: field.room_type,
  room_type_description: field.room_type_description,
  surnames: field.surnames,
  type: field.type,
  label:
    field.surnames != null || field.name != null
      ? (field.surnames != null ? field.surnames + ', ' : '') + field.name
      : 'Pasajero #' + field.sequence_number,
  document_url: field.document_url,
});

export const createFilePassengerCommunicationAdapter = (field) => ({
  given_name: field.name,
  surname: field.surnames,
  type: field.type,
});

export const createFileItineraryReplaceAdapter = (
  field,
  quantity,
  token_search,
  search_parameters,
  passengers
) => ({
  id: field.id,
  code: field.code,
  name: field.name,
  class: field.class,
  color_class: field.color_class,
  best_option_taken: field.best_option_taken,
  token_search: token_search,
  search_parameters: search_parameters,
  quantity: quantity,
  passengers: passengers,
  rooms: [],
});

export const createFileItineraryServiceReplaceAdapter = (
  field,
  rate,
  adults,
  children,
  quantity,
  price,
  token_search,
  search_parameters_services,
  passengers
) => {
  // Base del adaptador común para todos los casos

  // Evaluar el valor de `field.entity`
  if (field.entity && field.entity === 'service-temporary') {
    return {
      service_id: field.id,
      entity: 'service-temporary',
      service_ident: field.ident || null,
      service_mask: 0,
      date_from: search_parameters_services.date,
      quantity_adults: adults,
      quantity_child: children,
      code: field.object_code,
      name: field.name,
      quantity: quantity,
      passengers: passengers,
      rate: null,
      token_search: null,
      search_parameters_services: search_parameters_services,
      child_ages: search_parameters_services.quantity_persons?.age_childs || [],
      price: parseFloat(price).toFixed(2),
      reservation_time: null,
    };
  } else {
    return {
      service_id: field.id,
      entity: 'service-equivalence',
      service_ident: field.ident || null,
      service_mask: field.service_mask || 0,
      date_from: search_parameters_services.date,
      quantity_adults: adults,
      quantity_child: children,
      code: field.code,
      name: field.name,
      quantity: quantity,
      passengers: passengers,
      rate: rate,
      token_search: token_search,
      search_parameters_services: search_parameters_services,
      child_ages: search_parameters_services.quantity_persons?.age_childs || [],
      price: parseFloat(price).toFixed(2),
      reservation_time: null,
    };
  }
};

export const createFileItineraryRoomReplaceAdapter = (field) => ({
  room_id: field.room_id,
  room_type: field.room_type,
  description: field.description,
  occupation: field.occupation,
  best_price: field.best_price,
  rates: [],
});

export const createBasicFileAdapter = (field) => ({
  description: field.description,
  date_in: field.dateIn,
  client_id: field.clientId,
  client_code: field.clientCode,
  client_name: field.clientName,
  adults: field.adults,
  children: field.children,
  accommodation_sgl: field.accommodationSgl,
  accommodation_dbl: field.accommodationDbl,
  accommodation_tpl: field.accommodationTpl,
  file_category_id: field.fileCategoryId,
  generate_statement: field.generateStatement,
  reason_statement_id: field.reasonStatementId,
  lang: field.lang,
});
