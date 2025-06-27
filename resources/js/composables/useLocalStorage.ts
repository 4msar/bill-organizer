import { ref, watch } from 'vue';

export function useLocalStorage<T = string>(key: string, defaultValue: T) {
    // Create a reactive reference
    const storedValue = ref<T>(
        (() => {
            try {
                const item = window.localStorage.getItem(key);
                return (item ? JSON.parse(item) : defaultValue) as T;
            } catch (error) {
                console.error(`Error reading localStorage key "${key}":`, error);
                return defaultValue as T;
            }
        })(),
    );

    // Watch for changes and update localStorage
    watch(
        storedValue,
        (newValue) => {
            try {
                window.localStorage.setItem(key, JSON.stringify(newValue));
            } catch (error) {
                console.error(`Error setting localStorage key "${key}":`, error);
            }
        },
        { deep: true },
    );

    return storedValue;
}
