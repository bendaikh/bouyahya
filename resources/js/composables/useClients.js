import { ref } from 'vue'
import axios from 'axios'

const clients = ref([])
const isLoading = ref(false)
const error = ref(null)

// Map database fields (snake_case) to frontend fields (camelCase)
const mapClientFromApi = (apiClient) => {
    return {
        id: apiClient.id,
        codeClient: apiClient.code_client,
        raisonSociale: apiClient.raison_sociale,
        nomGerant: apiClient.nom_gerant,
        ville: apiClient.ville,
        typeClient: apiClient.type_client,
        modePaiement: apiClient.mode_paiement,
        echeance: apiClient.echeance,
        cin: apiClient.cin,
        ifFiscal: apiClient.if_fiscal,
        patente: apiClient.patente,
        cnss: apiClient.cnss,
        ice: apiClient.ice,
        banque: apiClient.banque,
        rib: apiClient.rib,
        plafond: apiClient.plafond ? parseFloat(apiClient.plafond) : null,
        bloquer: apiClient.bloquer || false
    }
}

// Map frontend fields (camelCase) to database fields (snake_case)
const mapClientToApi = (client) => {
    return {
        code_client: client.codeClient,
        raison_sociale: client.raisonSociale,
        nom_gerant: client.nomGerant,
        ville: client.ville,
        type_client: client.typeClient,
        mode_paiement: client.modePaiement,
        echeance: client.echeance,
        cin: client.cin || null,
        if_fiscal: client.ifFiscal || null,
        patente: client.patente || null,
        cnss: client.cnss || null,
        ice: client.ice || null,
        banque: client.banque || null,
        rib: client.rib || null,
        plafond: client.plafond || null,
        bloquer: client.bloquer || false
    }
}

const fetchClients = async () => {
    isLoading.value = true
    error.value = null
    try {
        const response = await axios.get('/api/clients')
        clients.value = response.data.map(mapClientFromApi)
    } catch (err) {
        error.value = err.message
        console.error('Error fetching clients:', err)
    } finally {
        isLoading.value = false
    }
}

const generateClientCode = async () => {
    try {
        const response = await axios.get('/api/clients/next-code')
        return response.data.code
    } catch (err) {
        console.error('Error generating client code:', err)
        // Fallback: generate locally
        const maxCode = clients.value.reduce((max, client) => {
            const numeric = parseInt(client.codeClient?.replace('C-', '') || '0', 10)
            return Math.max(max, numeric)
        }, 0)
        const next = maxCode + 1
        return `C-${String(next).padStart(4, '0')}`
    }
}

const createClient = async (payload) => {
    isLoading.value = true
    error.value = null
    try {
        const apiData = mapClientToApi(payload)
        const response = await axios.post('/api/clients', apiData)
        const newClient = mapClientFromApi(response.data)
        clients.value = [newClient, ...clients.value]
        return newClient
    } catch (err) {
        error.value = err.response?.data?.errors || err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const updateClient = async (clientId, updates) => {
    isLoading.value = true
    error.value = null
    try {
        const apiData = mapClientToApi({ ...updates, codeClient: updates.codeClient || clients.value.find(c => c.id === clientId)?.codeClient })
        const response = await axios.put(`/api/clients/${clientId}`, apiData)
        const updatedClient = mapClientFromApi(response.data)
        clients.value = clients.value.map((client) => {
            if (client.id === clientId) {
                return updatedClient
            }
            return client
        })
        return updatedClient
    } catch (err) {
        error.value = err.response?.data?.errors || err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const deleteClient = async (clientId) => {
    isLoading.value = true
    error.value = null
    try {
        await axios.delete(`/api/clients/${clientId}`)
        clients.value = clients.value.filter((client) => client.id !== clientId)
    } catch (err) {
        error.value = err.message
        throw err
    } finally {
        isLoading.value = false
    }
}

const getClientById = (clientId) => clients.value.find((client) => client.id === clientId)

export function useClients() {
    // Load clients on first use
    if (clients.value.length === 0 && !isLoading.value) {
        fetchClients()
    }

    return {
        clients,
        isLoading,
        error,
        fetchClients,
        createClient,
        updateClient,
        deleteClient,
        getClientById,
        generateClientCode
    }
}
