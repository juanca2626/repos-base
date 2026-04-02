import { formatDate } from '@/utils/files.js';

export const createItineraryAdapter = ({ itinerary, index }) => {
  const checkIn = formatDate(itinerary.check_in);
  const paxs = [
    itinerary.adult ? `${itinerary.adult} ADL` : '',
    itinerary.child ? `${itinerary.child} CHD` : '',
    itinerary.infant ? `${itinerary.infant} INF` : '',
  ]
    .filter(Boolean)
    .join(' /');
  return {
    // type: itinerary.type,
    // code: itinerary.code,
    // adult: itinerary.adult,
    // child: itinerary.child,
    // infant: itinerary.infant,
    // origin: itinerary.origin,
    // destiny: itinerary.destiny,
    // checkIn: itinerary.check_in,
    // checkInTime: itinerary.check_in_time,
    // timestamp: itinerary.timestamp,

    // custom fields
    key: index + 1,
    order: `${index + 1}.`,
    type: itinerary.type,
    total: itinerary.total_amount ? itinerary.total_amount : '',
    data: {
      title: itinerary.name,
      subtitle: `${checkIn} - ${paxs}`,
    },
  };
};
