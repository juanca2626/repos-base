export function useLocalStorage() {
  const saveValue = (key: string, value: unknown, useJson: boolean = true): void => {
    let _value: string;

    if (useJson) {
      _value = JSON.stringify(value);
    } else {
      _value = value === null || value === undefined ? '' : value.toString();
    }

    localStorage.setItem(key, _value);
  };

  const loadValue = (key: string, useJson: boolean = true) => {
    const storedValue = localStorage.getItem(key);

    if (storedValue) {
      return useJson ? JSON.parse(storedValue) : storedValue;
    }

    return null;
  };

  const removeValue = (key: string) => {
    localStorage.removeItem(key);
  };

  return {
    saveValue,
    loadValue,
    removeValue,
  };
}
