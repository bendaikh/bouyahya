<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Paramètres de l'application</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Gérez le nom de l'application, le logo, les villes, les familles d'articles et les unités de mesure
                </p>
            </div>
        </div>

        <!-- Success Message -->
        <div
            v-if="successMessage"
            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-200"
        >
            {{ successMessage }}
        </div>

        <!-- Error Message -->
        <div
            v-if="errorMessage"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
        >
            {{ errorMessage }}
        </div>

        <!-- App Name Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nom de l'application</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Modifiez le nom de votre application
                </p>
            </header>
            <div class="px-6 py-6">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="appName"
                            type="text"
                            class="input"
                            placeholder="Nom de l'application"
                        />
                    </div>
                    <button
                        @click="updateAppName"
                        :disabled="isUpdatingName"
                        class="btn-primary"
                    >
                        <svg
                            v-if="isUpdatingName"
                            class="mr-2 h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8" />
                        </svg>
                        {{ isUpdatingName ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </div>
        </section>

        <!-- App Logo Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Logo de l'application</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Téléchargez ou modifiez le logo de votre application
                </p>
            </header>
            <div class="px-6 py-6">
                <div class="flex items-center gap-6">
                    <!-- Logo Preview -->
                    <div class="flex-shrink-0">
                        <div
                            v-if="appLogo"
                            class="h-24 w-24 rounded-lg border-2 border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800 flex items-center justify-center"
                        >
                            <img
                                :src="getLogoUrl(appLogo)"
                                alt="Logo"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <div
                            v-else
                            class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-800"
                        >
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="flex-1 space-y-3">
                        <div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <div class="flex gap-3">
                                <button
                                    @click="$refs.fileInput.click()"
                                    class="btn-secondary"
                                    :disabled="isUploadingLogo"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Télécharger un logo
                                </button>
                                <button
                                    v-if="appLogo"
                                    @click="deleteLogo"
                                    class="btn-danger"
                                    :disabled="isDeletingLogo"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Formats acceptés: JPG, PNG, GIF, SVG (max. 2 Mo)
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cities Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Gestion des villes</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ajoutez ou supprimez les villes disponibles dans les formulaires
                </p>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add City Form -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="newCity"
                            type="text"
                            class="input"
                            placeholder="Nom de la ville"
                            @keyup.enter="addCity"
                        />
                    </div>
                    <button
                        @click="addCity"
                        :disabled="isAddingCity || !newCity.trim()"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Cities List -->
                <div v-if="cities.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="(city, index) in cities"
                        :key="index"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <span class="text-sm text-gray-900 dark:text-white">{{ city }}</span>
                        <button
                            @click="removeCity(city)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                            :disabled="removingCity === city"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Aucune ville configurée
                </div>
            </div>
        </section>

        <!-- Familles Article Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Familles d'articles</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Définissez les catégories principales pour vos articles
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add Famille Form -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="newFamille"
                            type="text"
                            class="input"
                            placeholder="Nom de la famille (ex: Électroménager, Informatique...)"
                            @keyup.enter="addFamille"
                        />
                    </div>
                    <button
                        @click="addFamille"
                        :disabled="isAddingFamille || !newFamille.trim()"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Familles List -->
                <div v-if="famillesArticle.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="famille in famillesArticle"
                        :key="famille.id"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                                {{ famille.nom.charAt(0).toUpperCase() }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ famille.nom }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                ({{ getSousFamillesCount(famille.id) }} sous-famille{{ getSousFamillesCount(famille.id) > 1 ? 's' : '' }})
                            </span>
                        </div>
                        <button
                            @click="removeFamille(famille)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            :disabled="removingFamille === famille.id"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Aucune famille d'articles configurée
                </div>
            </div>
        </section>

        <!-- Sous-Familles Article Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30">
                        <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sous-familles d'articles</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Organisez vos articles en sous-catégories au sein des familles
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add Sous-Famille Form -->
                <div class="flex gap-4">
                    <div class="w-1/3">
                        <select
                            v-model="selectedFamilleForSousFamille"
                            class="input"
                            :disabled="famillesArticle.length === 0"
                        >
                            <option value="">Sélectionner une famille</option>
                            <option v-for="famille in famillesArticle" :key="famille.id" :value="famille.id">
                                {{ famille.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <input
                            v-model="newSousFamille"
                            type="text"
                            class="input"
                            placeholder="Nom de la sous-famille (ex: Réfrigérateurs, Ordinateurs...)"
                            :disabled="!selectedFamilleForSousFamille"
                            @keyup.enter="addSousFamille"
                        />
                    </div>
                    <button
                        @click="addSousFamille"
                        :disabled="isAddingSousFamille || !newSousFamille.trim() || !selectedFamilleForSousFamille"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Alert if no familles -->
                <div v-if="famillesArticle.length === 0" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Vous devez d'abord créer au moins une famille d'articles
                    </div>
                </div>

                <!-- Sous-Familles List grouped by Famille -->
                <div v-else-if="sousFamillesArticle.length > 0" class="space-y-4">
                    <div v-for="famille in famillesArticle" :key="famille.id" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ famille.nom }}</span>
                        </div>
                        <div v-if="getSousFamillesByFamille(famille.id).length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="sousFamille in getSousFamillesByFamille(famille.id)"
                                :key="sousFamille.id"
                                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                            >
                                <div class="flex items-center gap-3 pl-4">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ sousFamille.nom }}</span>
                                </div>
                                <button
                                    @click="removeSousFamille(sousFamille)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    :disabled="removingSousFamille === sousFamille.id"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="px-4 py-3 text-sm text-gray-400 dark:text-gray-500 italic pl-8">
                            Aucune sous-famille
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Aucune sous-famille d'articles configurée
                </div>
            </div>
        </section>

        <!-- Unités de Mesure Section -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <header class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Unités de mesure</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Configurez les unités pour quantifier vos articles
                        </p>
                    </div>
                </div>
            </header>
            <div class="px-6 py-6 space-y-4">
                <!-- Add Unité Form -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input
                            v-model="newUniteNom"
                            type="text"
                            class="input"
                            placeholder="Nom de l'unité (ex: Kilogramme, Mètre, Litre...)"
                            @keyup.enter="addUnite"
                        />
                    </div>
                    <div class="w-32">
                        <input
                            v-model="newUniteAbreviation"
                            type="text"
                            class="input"
                            placeholder="Abrév. (kg, m...)"
                            @keyup.enter="addUnite"
                        />
                    </div>
                    <button
                        @click="addUnite"
                        :disabled="isAddingUnite || !newUniteNom.trim() || !newUniteAbreviation.trim()"
                        class="btn-primary"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter
                    </button>
                </div>

                <!-- Unités List -->
                <div v-if="unitesMesure.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="unite in unitesMesure"
                        :key="unite.id"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center justify-center h-8 min-w-[2.5rem] px-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase">
                                {{ unite.abreviation }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ unite.nom }}</span>
                        </div>
                        <button
                            @click="removeUnite(unite)"
                            class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            :disabled="removingUnite === unite.id"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    Aucune unité de mesure configurée
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const appName = ref('')
const appLogo = ref(null)
const cities = ref([])
const newCity = ref('')

// Familles article
const famillesArticle = ref([])
const newFamille = ref('')
const isAddingFamille = ref(false)
const removingFamille = ref(null)

// Sous-familles article
const sousFamillesArticle = ref([])
const newSousFamille = ref('')
const selectedFamilleForSousFamille = ref('')
const isAddingSousFamille = ref(false)
const removingSousFamille = ref(null)

// Unités de mesure
const unitesMesure = ref([])
const newUniteNom = ref('')
const newUniteAbreviation = ref('')
const isAddingUnite = ref(false)
const removingUnite = ref(null)

const isUpdatingName = ref(false)
const isUploadingLogo = ref(false)
const isDeletingLogo = ref(false)
const isAddingCity = ref(false)
const removingCity = ref(null)

const successMessage = ref('')
const errorMessage = ref('')

const fileInput = ref(null)

const showMessage = (message, isError = false) => {
    if (isError) {
        errorMessage.value = message
        successMessage.value = ''
    } else {
        successMessage.value = message
        errorMessage.value = ''
    }
    setTimeout(() => {
        successMessage.value = ''
        errorMessage.value = ''
    }, 3000)
}

const loadSettings = async () => {
    try {
        const response = await fetch('/api/settings')
        const data = await response.json()
        appName.value = data.app_name
        appLogo.value = data.app_logo
        cities.value = data.cities || []
        famillesArticle.value = data.familles_article || []
        sousFamillesArticle.value = data.sous_familles_article || []
        unitesMesure.value = data.unites_mesure || []
    } catch (error) {
        console.error('Erreur lors du chargement des paramètres:', error)
        showMessage('Erreur lors du chargement des paramètres', true)
    }
}

const updateAppName = async () => {
    if (!appName.value.trim()) {
        showMessage('Le nom de l\'application ne peut pas être vide', true)
        return
    }

    try {
        isUpdatingName.value = true
        const response = await fetch('/api/settings/app-name', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ app_name: appName.value })
        })

        if (response.ok) {
            showMessage('Nom de l\'application mis à jour avec succès')
            window.dispatchEvent(new CustomEvent('app-name-updated', { detail: appName.value }))
        } else {
            throw new Error('Erreur lors de la mise à jour')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la mise à jour du nom', true)
    } finally {
        isUpdatingName.value = false
    }
}

const handleFileChange = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('logo', file)

    try {
        isUploadingLogo.value = true
        const response = await fetch('/api/settings/logo', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })

        const data = await response.json()
        if (response.ok) {
            appLogo.value = data.logo_path
            showMessage('Logo téléchargé avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors du téléchargement')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors du téléchargement du logo', true)
    } finally {
        isUploadingLogo.value = false
        fileInput.value.value = ''
    }
}

const deleteLogo = async () => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer le logo ?')) return

    try {
        isDeletingLogo.value = true
        const response = await fetch('/api/settings/logo', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })

        if (response.ok) {
            appLogo.value = null
            showMessage('Logo supprimé avec succès')
        } else {
            throw new Error('Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression du logo', true)
    } finally {
        isDeletingLogo.value = false
    }
}

const addCity = async () => {
    if (!newCity.value.trim()) return

    try {
        isAddingCity.value = true
        const response = await fetch('/api/settings/cities/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ city: newCity.value.trim() })
        })

        const data = await response.json()
        if (response.ok) {
            cities.value = data.cities
            newCity.value = ''
            showMessage('Ville ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de l\'ajout de la ville', true)
    } finally {
        isAddingCity.value = false
    }
}

const removeCity = async (city) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer "${city}" ?`)) return

    try {
        removingCity.value = city
        const response = await fetch('/api/settings/cities/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ city })
        })

        const data = await response.json()
        if (response.ok) {
            cities.value = data.cities
            showMessage('Ville supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage('Erreur lors de la suppression de la ville', true)
    } finally {
        removingCity.value = null
    }
}

const getLogoUrl = (path) => {
    if (!path) return ''
    return `/storage/${path}`
}

// =====================================================
// FAMILLES ARTICLE
// =====================================================

const addFamille = async () => {
    if (!newFamille.value.trim()) return

    try {
        isAddingFamille.value = true
        const response = await fetch('/api/settings/familles-article/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ nom: newFamille.value.trim() })
        })

        const data = await response.json()
        if (response.ok) {
            famillesArticle.value = data.familles
            newFamille.value = ''
            showMessage('Famille ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'ajout de la famille', true)
    } finally {
        isAddingFamille.value = false
    }
}

const removeFamille = async (famille) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer la famille "${famille.nom}" ?`)) return

    try {
        removingFamille.value = famille.id
        const response = await fetch('/api/settings/familles-article/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: famille.id })
        })

        const data = await response.json()
        if (response.ok) {
            famillesArticle.value = data.familles
            showMessage('Famille supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de la suppression de la famille', true)
    } finally {
        removingFamille.value = null
    }
}

const getSousFamillesCount = (familleId) => {
    return sousFamillesArticle.value.filter(sf => sf.famille_id === familleId).length
}

// =====================================================
// SOUS-FAMILLES ARTICLE
// =====================================================

const getSousFamillesByFamille = (familleId) => {
    return sousFamillesArticle.value.filter(sf => sf.famille_id === familleId)
}

const addSousFamille = async () => {
    if (!newSousFamille.value.trim() || !selectedFamilleForSousFamille.value) return

    try {
        isAddingSousFamille.value = true
        const response = await fetch('/api/settings/sous-familles-article/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                nom: newSousFamille.value.trim(),
                famille_id: selectedFamilleForSousFamille.value
            })
        })

        const data = await response.json()
        if (response.ok) {
            sousFamillesArticle.value = data.sous_familles
            newSousFamille.value = ''
            showMessage('Sous-famille ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'ajout de la sous-famille', true)
    } finally {
        isAddingSousFamille.value = false
    }
}

const removeSousFamille = async (sousFamille) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer la sous-famille "${sousFamille.nom}" ?`)) return

    try {
        removingSousFamille.value = sousFamille.id
        const response = await fetch('/api/settings/sous-familles-article/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: sousFamille.id })
        })

        const data = await response.json()
        if (response.ok) {
            sousFamillesArticle.value = data.sous_familles
            showMessage('Sous-famille supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de la suppression de la sous-famille', true)
    } finally {
        removingSousFamille.value = null
    }
}

// =====================================================
// UNITES DE MESURE
// =====================================================

const addUnite = async () => {
    if (!newUniteNom.value.trim() || !newUniteAbreviation.value.trim()) return

    try {
        isAddingUnite.value = true
        const response = await fetch('/api/settings/unites-mesure/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                nom: newUniteNom.value.trim(),
                abreviation: newUniteAbreviation.value.trim()
            })
        })

        const data = await response.json()
        if (response.ok) {
            unitesMesure.value = data.unites
            newUniteNom.value = ''
            newUniteAbreviation.value = ''
            showMessage('Unité de mesure ajoutée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de l\'ajout')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de l\'ajout de l\'unité', true)
    } finally {
        isAddingUnite.value = false
    }
}

const removeUnite = async (unite) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'unité "${unite.nom}" ?`)) return

    try {
        removingUnite.value = unite.id
        const response = await fetch('/api/settings/unites-mesure/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: unite.id })
        })

        const data = await response.json()
        if (response.ok) {
            unitesMesure.value = data.unites
            showMessage('Unité de mesure supprimée avec succès')
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression')
        }
    } catch (error) {
        console.error('Erreur:', error)
        showMessage(error.message || 'Erreur lors de la suppression de l\'unité', true)
    } finally {
        removingUnite.value = null
    }
}

onMounted(() => {
    loadSettings()
})
</script>

<style scoped>
@reference '../../css/app.css';

.input {
    @apply w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100;
}

.btn-primary {
    @apply inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-400;
}

.btn-secondary {
    @apply inline-flex items-center justify-center rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50;
}

.btn-danger {
    @apply inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>

