import { ref } from 'vue'
import axios from 'axios'

export function useUsers() {
    const users = ref([])
    const isLoading = ref(false)
    const error = ref(null)

    const fetchUsers = async () => {
        isLoading.value = true
        try {
            const response = await axios.get('/api/users')
            users.value = response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors du chargement des utilisateurs'
            console.error(err)
        } finally {
            isLoading.value = false
        }
    }

    const createUser = async (userData) => {
        isLoading.value = true
        try {
            const response = await axios.post('/api/users', userData)
            users.value.unshift(response.data)
            return response.data
        } catch (err) {
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const updateUser = async (id, userData) => {
        isLoading.value = true
        try {
            const response = await axios.put(`/api/users/${id}`, userData)
            const index = users.value.findIndex(u => u.id === id)
            if (index !== -1) {
                users.value[index] = response.data
            }
            return response.data
        } catch (err) {
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const deleteUser = async (id) => {
        isLoading.value = true
        try {
            await axios.delete(`/api/users/${id}`)
            users.value = users.value.filter(u => u.id !== id)
        } catch (err) {
            throw err
        } finally {
            isLoading.value = false
        }
    }

    return {
        users,
        isLoading,
        error,
        fetchUsers,
        createUser,
        updateUser,
        deleteUser
    }
}
