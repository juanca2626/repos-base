const AUDIO_CONFIG = {
  url: 'https://res.cloudinary.com/litodti/video/upload/aurora/maca/maca-step.mp3',
  volume: 0.3,
  playbackRate: 0.75,
  debug: false,
};

// Pre-load audio once when module is imported
const audio = new Audio(AUDIO_CONFIG.url);
audio.loop = true;
audio.volume = AUDIO_CONFIG.volume;
audio.playbackRate = AUDIO_CONFIG.playbackRate;
audio.preload = 'auto';

let instanceCount = 0;

const log = (...args) => AUDIO_CONFIG.debug && console.error('[MacaAudio]', ...args);

export const useMacaAudio = () => {
  const startAudio = () => {
    instanceCount++;
    log(`startAudio called. Instance count: ${instanceCount}`);

    if (instanceCount >= 1) {
      log('Starting audio playback');
      audio.currentTime = 0; // Reset to start
      audio
        .play()
        .then(() => log('Audio playing successfully'))
        .catch((err) => console.warn('[MacaAudio] Playback failed:', err));
    } else {
      log('Audio already playing');
    }
  };

  const stopAudio = () => {
    if (instanceCount > 0) {
      instanceCount--;
      log(`stopAudio called. Instance count: ${instanceCount}`);
    }

    if (instanceCount === 0) {
      log('Pausing audio');
      try {
        audio.pause();
        audio.currentTime = 0;
      } catch (error) {
        console.error('[MacaAudio] Error pausing audio:', error);
      }
    }
  };

  return {
    startAudio,
    stopAudio,
  };
};
