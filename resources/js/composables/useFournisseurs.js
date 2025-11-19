import { ref, watch } from 'vue'

const STORAGE_KEY = 'bouyahya-fournisseurs'

const defaultFournisseurs = [
    {
        id: 'fournisseur-1',
        codeFournisseur: 'F-0001',
        nomFournisseur: 'Distributions Alpha',
        nomGerant: 'Ahmed Benali',
        telephone: '0612345678',
        email: 'contact@alpha-dist.com',
        activite: 'Distribution de produits alimentaires',
        ville: 'Casablanca',
        ice: 'ICE-1234567890',
        modePaiement: 'Virement bancaire'
    },
    {
        id: 'fournisseur-2',
        codeFournisseur: 'F-0002',
        nomFournisseur: 'Tech Solutions',
        nomGerant: 'Fatima Alami',
        telephone: '0623456789',
        email: 'info@techsol.ma',
        activite: 'Fourniture de matériel informatique',
        ville: 'Rabat',
        ice: 'ICE-2233445566',
        modePaiement: 'Chèque'
    }
]

const getInitialFournisseurs = () => {
    if (typeof window === 'undefined') {
        return [...defaultFournisseurs]
    }

    try {
        const stored = window.localStorage.getItem(STORAGE_KEY)
        if (stored) {
            return JSON.parse(stored)
        }
    } catch (error) {
        console.error('Unable to load fournisseurs from localStorage', error)
    }

    return [...defaultFournisseurs]
}

const fournisseurs = ref(getInitialFournisseurs())

const persistFournisseurs = () => {
    if (typeof window === 'undefined') {
        return
    }

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(fournisseurs.value))
    } catch (error) {
        console.error('Unable to save fournisseurs to localStorage', error)
    }
}

watch(
    fournisseurs,
    () => {
        persistFournisseurs()
    },
    { deep: true }
)

const generateId = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID()
    }
    return `fournisseur-${Math.random().toString(36).slice(2, 11)}`
}

const parseCodeNumber = (code) => {
    if (!code) {
        return 0
    }
    const numeric = parseInt(code.replace('F-', ''), 10)
    return Number.isNaN(numeric) ? 0 : numeric
}

const generateFournisseurCode = () => {
    const maxCode = fournisseurs.value.reduce((max, fournisseur) => {
        return Math.max(max, parseCodeNumber(fournisseur.codeFournisseur))
    }, 0)
    const next = maxCode + 1
    return `F-${String(next).padStart(5, '0')}`
}

const createFournisseur = (payload) => {
    const newFournisseur = {
        ...payload,
        id: generateId(),
        codeFournisseur: payload.codeFournisseur || generateFournisseurCode()
    }
    fournisseurs.value = [newFournisseur, ...fournisseurs.value]
    return newFournisseur
}

const updateFournisseur = (fournisseurId, updates) => {
    fournisseurs.value = fournisseurs.value.map((fournisseur) => {
        if (fournisseur.id === fournisseurId) {
            return { ...fournisseur, ...updates }
        }
        return fournisseur
    })
}

const deleteFournisseur = (fournisseurId) => {
    fournisseurs.value = fournisseurs.value.filter((fournisseur) => fournisseur.id !== fournisseurId)
}

const getFournisseurById = (fournisseurId) => fournisseurs.value.find((fournisseur) => fournisseur.id === fournisseurId)

export function useFournisseurs() {
    return {
        fournisseurs,
        createFournisseur,
        updateFournisseur,
        deleteFournisseur,
        getFournisseurById,
        generateFournisseurCode
    }
}

