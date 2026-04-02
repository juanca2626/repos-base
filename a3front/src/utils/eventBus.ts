import mitt from 'mitt';

export type Events = {
  'open-drawer': void; // Definimos el evento sin payload
};

const eventBus = mitt<Events>();

export default eventBus;
