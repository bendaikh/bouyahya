import { ref, computed } from 'vue'

// Cached user reference (shared across all components)
const cachedUser = ref(null)

/**
 * Composable for checking user permissions
 * Extracts the permission logic from Sidebar.vue to be reusable across all components
 */
export function usePermissions() {
    // Load user from DOM if not already cached
    if (!cachedUser.value) {
        const appEl = document.getElementById('app')
        if (appEl && appEl.dataset.user) {
            try {
                cachedUser.value = JSON.parse(appEl.dataset.user)
            } catch (e) {
                console.error('Error parsing user data:', e)
            }
        }
    }

    const user = cachedUser

    /**
     * Check if the current user has a specific permission
     * @param {string|string[]} permission - Permission name or array of permission names
     * @returns {boolean} - True if user has the permission
     */
    const hasPermission = (permission) => {
        if (!user.value) return false
        
        // Fallback for legacy superadmin role column
        if (user.value.role === 'superadmin') return true
        
        if (!user.value.roles) return false
        
        // Superadmin role name check
        if (user.value.roles.some(role => role.name === 'superadmin')) return true
        
        // Extract all permission names
        const userPermissions = (user.value?.roles || []).flatMap(role => {
            if (!role || !role.permissions) return []
            return role.permissions.map(p => typeof p === 'string' ? p : (p?.name || ''))
        })

        const checkSingle = (p) => {
            if (!p) return true
            
            // Direct match
            if (userPermissions.includes(p)) return true
            
            // Granular to legacy/module mapping (e.g. user has 'manage achats', we check 'achats.view')
            if (p.startsWith('achats.') && userPermissions.includes('manage achats')) return true
            if (p.startsWith('ventes.') && userPermissions.includes('manage ventes')) return true
            if (p.startsWith('stock.') && userPermissions.includes('manage stock')) return true
            if (p.startsWith('tresorerie.') && userPermissions.includes('manage tresorerie')) return true
            if (p.startsWith('contacts.clients.') && userPermissions.includes('manage clients')) return true
            if (p.startsWith('contacts.fournisseurs.') && userPermissions.includes('manage fournisseurs')) return true
            if ((p.startsWith('admin.users.') || p.startsWith('admin.roles.')) && userPermissions.includes('manage users')) return true
            if (p.startsWith('admin.settings.') && userPermissions.includes('manage settings')) return true

            // Legacy/Module to granular mapping (e.g. user has 'achats.view', we check 'manage achats')
            if (p === 'manage achats' && userPermissions.some(up => up.startsWith('achats.'))) return true
            if (p === 'manage ventes' && userPermissions.some(up => up.startsWith('ventes.'))) return true
            if (p === 'manage stock' && userPermissions.some(up => up.startsWith('stock.'))) return true
            if (p === 'manage tresorerie' && userPermissions.some(up => up.startsWith('tresorerie.'))) return true
            if (p === 'manage clients' && userPermissions.some(up => up.startsWith('contacts.clients.'))) return true
            if (p === 'manage fournisseurs' && userPermissions.some(up => up.startsWith('contacts.fournisseurs.'))) return true
            if (p === 'manage users' && userPermissions.some(up => up.startsWith('admin.users.') || up.startsWith('admin.roles.'))) return true
            if (p === 'manage settings' && userPermissions.some(up => up.startsWith('admin.settings.'))) return true
            
            return false
        }

        if (Array.isArray(permission)) {
            return permission.some(p => checkSingle(p))
        }
        
        return checkSingle(permission)
    }

    /**
     * Check if user has any of the specified permissions
     * @param {string[]} permissions - Array of permission names
     * @returns {boolean}
     */
    const hasAnyPermission = (permissions) => {
        return permissions.some(p => hasPermission(p))
    }

    /**
     * Check if user has all of the specified permissions
     * @param {string[]} permissions - Array of permission names
     * @returns {boolean}
     */
    const hasAllPermissions = (permissions) => {
        return permissions.every(p => hasPermission(p))
    }

    /**
     * Check if user is superadmin
     * @returns {boolean}
     */
    const isSuperAdmin = computed(() => {
        if (!user.value) return false
        if (user.value.role === 'superadmin') return true
        return user.value.roles?.some(role => role.name === 'superadmin') || false
    })

    /**
     * Get the current user
     * @returns {Object|null}
     */
    const getUser = () => user.value

    /**
     * Refresh user data from DOM (useful after login or permission changes)
     */
    const refreshUser = () => {
        const appEl = document.getElementById('app')
        if (appEl && appEl.dataset.user) {
            try {
                cachedUser.value = JSON.parse(appEl.dataset.user)
            } catch (e) {
                console.error('Error parsing user data:', e)
            }
        }
    }

    return {
        user,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        isSuperAdmin,
        getUser,
        refreshUser
    }
}
