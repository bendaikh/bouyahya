import { ref } from 'vue'
import axios from 'axios'

const fournisseurs = ref([])
const isLoading = ref(false)
const error = ref(null)

// Map database fields (snake_case) to frontend fields (camelCase)
const mapFournisseurFromApi = (apiFournisseur) => {
    return {
        id: apiFournisseur.id,
        codeFournisseur: apiFournisseur.code_fournisseur,
        nomFournisseur: apiFournisseur.nom_fournisseur,
        nomGerant: apiFournisseur.nom_gerant,
        telephone: apiFournisseur.telephone,
        email: apiFournisseur.email,
        activite: apiFournisseur.activite,
        ville: apiFournisseur.ville,
        ice: apiFournisseur.ice,
        modePaiement: apiFournisseur.mode_paiement
    }
}

// Map frontend fields (camelCase) to database fields (snake_case)
const mapFournisseurToApi = (fournisseur) => {
    return {
        code_fournisseur: fournisseur.codeFournisseur,
        nom_fournisseur: fournisseur.nomFournisseur,
        nom_gerant: fournisseur.nomGerant,
        telephone: fournisseur.telephone,
        email: fournisseur.email || null,
        activite: fournisseur.activite || null,
        ville: fournisseur.ville || null,
        ice: fournisseur.ice || null,
        mode_paiement: fournisseur.modePaiement || null
    }
}

const fetchFournisseurs = async () => {
    isLoading.value = true
    error.value = null
    try {
        const response = await axios.get('/api/fournisseurs')
        fournisseurs.value = response.data.map(mapFournisseurFromApi)
    } catch (err) {
        error.value = err.message
        console.error('Error fetching fournisseurs:', err)
    } finally {
        isLoading.value = false
    }
}

const generateFournisseurCode = async () => {
    try {
        const response = await axios.get('/api/fournisseurs/next-code')
        return response.data.code
    } catch (err) {
        console.error('Error generating fournisseur code:', err)
        // Fallback: generate locally
        const maxCode = fournisseurs.value.reduce((max, fournisseur) => {
            const numeric = parseInt(fournisseur.codeFournisseur?.replace('F-', '') || '0', 10)
            return Math.max(max, numeric)
        }, 0)
        const next = maxCode + 1
        return `F-${String(next).padStart(5, '0')}`
    }
}

const createFournisseur = async (payload) => {
    isLoading.value = true
    error.value = null
    try {
        const apiData = mapFournisseurToApi(payload)
        const response = await axios.post('/api/fournisseurs', apiData)
        const newFournisseur = mapFournisseurFromApi(response.data)
        fournisseurs.value = [newFournisseur, ...fournisseurs.value]
        return newFournisseur
    } catch (err) {
        error.value = err.response?.data?.errors || err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const updateFournisseur = async (fournisseurId, updates) => {
    isLoading.value = true
    error.value = null
    try {
        const apiData = mapFournisseurToApi({ 
            ...updates, 
            codeFournisseur: updates.codeFournisseur || fournisseurs.value.find(f => f.id === fournisseurId)?.codeFournisseur 
        })
        const response = await axios.put(`/api/fournisseurs/${fournisseurId}`, apiData)
        const updatedFournisseur = mapFournisseurFromApi(response.data)
        fournisseurs.value = fournisseurs.value.map((fournisseur) => {
            if (fournisseur.id === fournisseurId) {
                return updatedFournisseur
            }
            return fournisseur
        })
        return updatedFournisseur
    } catch (err) {
        error.value = err.response?.data?.errors || err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const deleteFournisseur = async (fournisseurId) => {
    isLoading.value = true
    error.value = null
    try {
        await axios.delete(`/api/fournisseurs/${fournisseurId}`)
        fournisseurs.value = fournisseurs.value.filter((fournisseur) => fournisseur.id !== fournisseurId)
    } catch (err) {
        error.value = err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const getFournisseurById = (fournisseurId) => fournisseurs.value.find((fournisseur) => fournisseur.id === fournisseurId)

export function useFournisseurs() {
    // Load fournisseurs on first use
    if (fournisseurs.value.length === 0 && !isLoading.value) {
        fetchFournisseurs()
    }

    return {
        fournisseurs,
        isLoading,
        error,
        fetchFournisseurs,
        createFournisseur,
        updateFournisseur,
        deleteFournisseur,
        getFournisseurById,
        generateFournisseurCode
    }
}
