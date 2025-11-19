import { ref, watch } from 'vue'

const STORAGE_KEY = 'bouyahya-clients'

const defaultClients = [
    {
        id: 'client-1',
        codeClient: 'C-0001',
        raisonSociale: 'Alpha Distribution',
        nomGerant: 'Karim Lahlou',
        ville: 'Casablanca',
        typeClient: 'Société',
        modePaiement: 'Virement',
        echeance: '30j',
        cin: 'AA123456',
        ifFiscal: '123456789',
        patente: 'P-98765',
        cnss: 'CNSS-456789',
        ice: 'ICE-1234567890',
        banque: 'Banque Populaire',
        rib: '123 456 789 000000000000 12',
        plafond: 250000,
        bloquer: false
    },
    {
        id: 'client-2',
        codeClient: 'C-0002',
        raisonSociale: 'Beldi Market',
        nomGerant: 'Fatima Zahra',
        ville: 'Marrakech',
        typeClient: 'Particulier',
        modePaiement: 'Espèces',
        echeance: '0j',
        cin: 'BB654321',
        ifFiscal: '2233445566',
        patente: 'P-12345',
        cnss: 'CNSS-123456',
        ice: 'ICE-2233445566',
        banque: 'Attijariwafa Bank',
        rib: '321 654 987 111111111111 98',
        plafond: 80000,
        bloquer: false
    },
    {
        id: 'client-3',
        codeClient: 'C-0003',
        raisonSociale: 'Tech Horizon',
        nomGerant: 'Youssef Haddad',
        ville: 'Rabat',
        typeClient: 'Société',
        modePaiement: 'Chèque',
        echeance: '45j',
        cin: 'CC789123',
        ifFiscal: '9988776655',
        patente: 'P-54321',
        cnss: 'CNSS-654321',
        ice: 'ICE-9988776655',
        banque: 'CIH Bank',
        rib: '456 789 123 222222222222 45',
        plafond: 150000,
        bloquer: true
    }
]

const getInitialClients = () => {
    if (typeof window === 'undefined') {
        return [...defaultClients]
    }

    try {
        const stored = window.localStorage.getItem(STORAGE_KEY)
        if (stored) {
            return JSON.parse(stored)
        }
    } catch (error) {
        console.error('Unable to load clients from localStorage', error)
    }

    return [...defaultClients]
}

const clients = ref(getInitialClients())

const persistClients = () => {
    if (typeof window === 'undefined') {
        return
    }

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(clients.value))
    } catch (error) {
        console.error('Unable to save clients to localStorage', error)
    }
}

watch(
    clients,
    () => {
        persistClients()
    },
    { deep: true }
)

const generateId = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID()
    }
    return `client-${Math.random().toString(36).slice(2, 11)}`
}

const parseCodeNumber = (code) => {
    if (!code) {
        return 0
    }
    const numeric = parseInt(code.replace('C-', ''), 10)
    return Number.isNaN(numeric) ? 0 : numeric
}

const generateClientCode = () => {
    const maxCode = clients.value.reduce((max, client) => {
        return Math.max(max, parseCodeNumber(client.codeClient))
    }, 0)
    const next = maxCode + 1
    return `C-${String(next).padStart(4, '0')}`
}

const createClient = (payload) => {
    const newClient = {
        ...payload,
        id: generateId(),
        codeClient: payload.codeClient || generateClientCode()
    }
    clients.value = [newClient, ...clients.value]
    return newClient
}

const updateClient = (clientId, updates) => {
    clients.value = clients.value.map((client) => {
        if (client.id === clientId) {
            return { ...client, ...updates }
        }
        return client
    })
}

const deleteClient = (clientId) => {
    clients.value = clients.value.filter((client) => client.id !== clientId)
}

const getClientById = (clientId) => clients.value.find((client) => client.id === clientId)

export function useClients() {
    return {
        clients,
        createClient,
        updateClient,
        deleteClient,
        getClientById,
        generateClientCode
    }
}


