import { ref } from 'vue';

const eventBus = ref(new Map());

const emit = (event: string, ...args) => {
  if (!eventBus.value.has(event)) return;
  eventBus.value.get(event).forEach((callback) => callback(...args));
};

const on = (event: string, callback) => {
  if (!eventBus.value.has(event)) {
    eventBus.value.set(event, []);
  }
  eventBus.value.get(event).push(callback);
};

const off = (event: string, callback) => {
  if (!eventBus.value.has(event)) return;
  eventBus.value.set(
    event,
    eventBus.value.get(event).filter((cb) => cb !== callback)
  );
};

export { emit, on, off };
