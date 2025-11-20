import { ref } from 'vue'

export function useSettings() {
    const settings = ref({
        app_name: 'Bouyahya',
        app_logo: null,
        cities: []
    })
    const isLoading = ref(false)
    const error = ref(null)

    /**
     * Fetch all settings
     */
    const fetchSettings = async () => {
        isLoading.value = true
        error.value = null
        
        try {
            const response = await fetch('/api/settings')
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des paramètres')
            }
            const data = await response.json()
            settings.value = data
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Fetch only cities
     */
    const fetchCities = async () => {
        try {
            const data = await fetchSettings()
            return data.cities || []
        } catch (err) {
            return []
        }
    }

    /**
     * Update app name
     */
    const updateAppName = async (appName) => {
        isLoading.value = true
        error.value = null
        
        try {
            const response = await fetch('/api/settings/app-name', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ app_name: appName })
            })
            
            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour du nom')
            }
            
            const data = await response.json()
            settings.value.app_name = data.app_name
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Upload logo
     */
    const uploadLogo = async (file) => {
        isLoading.value = true
        error.value = null
        
        try {
            const formData = new FormData()
            formData.append('logo', file)
            
            const response = await fetch('/api/settings/logo', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            
            if (!response.ok) {
                throw new Error('Erreur lors du téléchargement du logo')
            }
            
            const data = await response.json()
            settings.value.app_logo = data.logo_path
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Delete logo
     */
    const deleteLogo = async () => {
        isLoading.value = true
        error.value = null
        
        try {
            const response = await fetch('/api/settings/logo', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            
            if (!response.ok) {
                throw new Error('Erreur lors de la suppression du logo')
            }
            
            settings.value.app_logo = null
            return await response.json()
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Add a city
     */
    const addCity = async (city) => {
        isLoading.value = true
        error.value = null
        
        try {
            const response = await fetch('/api/settings/cities/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ city })
            })
            
            if (!response.ok) {
                throw new Error('Erreur lors de l\'ajout de la ville')
            }
            
            const data = await response.json()
            settings.value.cities = data.cities
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    /**
     * Remove a city
     */
    const removeCity = async (city) => {
        isLoading.value = true
        error.value = null
        
        try {
            const response = await fetch('/api/settings/cities/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ city })
            })
            
            if (!response.ok) {
                throw new Error('Erreur lors de la suppression de la ville')
            }
            
            const data = await response.json()
            settings.value.cities = data.cities
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur:', err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    return {
        settings,
        isLoading,
        error,
        fetchSettings,
        fetchCities,
        updateAppName,
        uploadLogo,
        deleteLogo,
        addCity,
        removeCity
    }
}

