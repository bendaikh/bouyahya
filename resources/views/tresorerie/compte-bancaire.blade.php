@extends('layouts.app')

@section('title', 'Trésorerie - Bouyahya')

@section('content')
<div x-data="compteTresorerieApp()" class="space-y-6">

<script>
function compteTresorerieApp() {
    return {
        showForm: false,
        isSubmitting: false,
        formData: {
            date_creation: new Date().toISOString().split('T')[0],
            code: '',
            libelle: '',
            type_compte: 'banque', // banque | caisse
            agence: '',
            ville: '',
            adresse: '',
            solde_initial: 0
        },

        async init() {
            await this.fetchNextCode();
        },

        async fetchNextCode() {
            try {
                const res = await fetch('{{ route('tresorerie.compte-bancaire.next-code') }}');
                const data = await res.json();
                this.formData.code = data.code || '';
            } catch (e) {
                console.error(e);
            }
        },

        openNewForm() {
            this.showForm = true;
            this.resetForm();
        },

        resetForm() {
            this.formData.date_creation = new Date().toISOString().split('T')[0];
            this.formData.libelle = '';
            this.formData.type_compte = 'banque';
            this.formData.agence = '';
            this.formData.ville = '';
            this.formData.adresse = '';
            this.formData.solde_initial = 0;
            this.fetchNextCode();
        },

        closeForm() {
            this.showForm = false;
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(value || 0));
        },

        async submitForm() {
            this.isSubmitting = true;
            try {
                const res = await fetch('{{ route('tresorerie.compte-bancaire.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                const data = await res.json();

                if (res.ok) {
                    alert(data.message || 'Compte créé avec succès');
                    window.location.reload();
                } else {
                    alert('Erreur: ' + (data.error || JSON.stringify(data.errors || data)));
                }
            } catch (e) {
                alert('Erreur: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteCompte(id, code) {
            if (!confirm(`Supprimer le compte ${code} ?`)) return;
            try {
                const res = await fetch(`/tresorerie/compte-bancaire/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (res.ok) {
                    alert(data.message || 'Supprimé');
                    window.location.reload();
                } else {
                    alert(data.error || 'Erreur lors de la suppression');
                }
            } catch (e) {
                alert('Erreur: ' + e.message);
            }
        }
    }
}
</script>

    <!-- Form -->
    <div x-show="showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <!-- Dark header like system -->
        <div class="bg-gray-900 dark:bg-gray-900 px-5 py-3 flex items-center justify-between">
            <div class="text-white font-semibold">Fiche Compte Trésorerie</div>
            <div class="px-3 py-1 rounded-md bg-gray-800 border border-gray-700 text-gray-100 text-sm font-mono" x-text="formData.code"></div>
        </div>

        <div class="p-6 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-2xl mx-auto">
                <!-- Date de création -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Date de Création</label>
                    <input type="date" x-model="formData.date_creation" class="w-full md:w-64 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Compte de trésorerie -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Compte de Trésorerie :</label>
                    <input type="text" x-model="formData.libelle" placeholder="Banque ou Caisse" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Type radio -->
    <div class="mb-6">
                    <div class="flex items-center gap-8 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3">
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="type_compte" value="banque" x-model="formData.type_compte" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium">Banque</span>
                        </label>
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="type_compte" value="caisse" x-model="formData.type_compte" class="text-blue-600 focus:ring-blue-500">
                            <span class="font-medium">Caisse</span>
                        </label>
                    </div>
                </div>

                <!-- Agence / Ville -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Agence:</label>
                        <input type="text" x-model="formData.agence" placeholder="Agence de la Banque" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Ville:</label>
                        <input type="text" x-model="formData.ville" placeholder="Ville de la Banque" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Adresse -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Adresse:</label>
                    <textarea x-model="formData.adresse" rows="3" placeholder="Adresse de l'Agence" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Solde initial -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Solde Initial:</label>
                    <div class="relative">
                        <input type="number" step="0.01" x-model="formData.solde_initial" class="w-full px-3 py-3 text-center text-xl font-semibold bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300 font-semibold">DH</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button @click="submitForm()" type="button" :disabled="isSubmitting" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span x-show="!isSubmitting">Valider</span>
                        <span x-show="isSubmitting">Enregistrement...</span>
                    </button>
                    <button @click="closeForm()" type="button" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Annuler</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Listing -->
    <div x-show="!showForm" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Trésorerie</h2>
            <p class="text-gray-600 dark:text-gray-400">Création et liste des comptes de trésorerie</p>
        </div>

        <div class="flex justify-end mb-4">
            <button @click="openNewForm()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Nouveau compte
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Compte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ville</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($comptes as $compte)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">{{ $compte->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $compte->date_creation?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $compte->libelle }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $compte->type_compte === 'banque' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ ucfirst($compte->type_compte) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $compte->agence ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $compte->ville ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">{{ number_format($compte->solde_actuel, 2, ',', ' ') }} DH</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="deleteCompte({{ $compte->id }}, '{{ $compte->code }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Aucun compte de trésorerie trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

