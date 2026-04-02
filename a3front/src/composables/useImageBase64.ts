import { ref } from 'vue';

export function useImageBase64() {
  const base64 = ref<string | null>(null); // esto tendrá solo la parte pura
  const loading = ref(false);
  const error = ref<string | null>(null);

  const toBase64 = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = () => {
        const result = reader.result as string;
        const pureBase64 = result.split(',')[1]; // quitar el prefijo MIME
        resolve(pureBase64);
      };

      reader.onerror = (err) => reject(err);

      reader.readAsDataURL(file);
    });
  };

  const convert = async (file: File) => {
    loading.value = true;
    error.value = null;

    try {
      base64.value = await toBase64(file);
    } catch (err) {
      console.log(err);
      error.value = 'Error al convertir la imagen';
      base64.value = null;
    } finally {
      loading.value = false;
    }
  };

  return {
    base64, // solo la parte codificada
    loading,
    error,
    convert,
  };
}
