import { ref } from 'vue'
import yaml from 'js-yaml'

const config = ref(null)

export function useConfig() {
  const loadConfig = async () => {
    try {
      const response = await fetch('/src/config.yaml')
      const yamlText = await response.text()
      config.value = yaml.load(yamlText)
      return config.value
    } catch (error) {
      console.error('Failed to load config:', error)
      // Fallback config
      config.value = {
        api: {
          baseUrl: 'http://localhost:8000/api',
          timeout: 10000
        },
        tournament: {
          totalTables: 6,
          seatsPerTable: 9,
          totalSeats: 54
        },
        ui: {
          theme: 'dark',
          animationDuration: 300,
          toastDuration: 5000
        }
      }
      return config.value
    }
  }

  const getConfig = () => {
    if (!config.value) {
      throw new Error('Config not loaded. Call loadConfig() first.')
    }
    return config.value
  }

  return {
    config,
    loadConfig,
    getConfig
  }
}

