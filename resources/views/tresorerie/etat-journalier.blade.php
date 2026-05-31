@extends('layouts.app')

@section('title', 'État journalier')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">État journalier</h2>
        <p class="text-gray-600 dark:text-gray-400">Consulter l'état journalier de la trésorerie</p>
    </div>
    
    <div class="space-y-4">
        <div class="flex justify-end">
            <input type="date" value="{{ date('Y-m-d') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-600 dark:text-gray-400">Encaissements</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">€5,250.00</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-4 border-l-4 border-red-500">
                <p class="text-sm text-gray-600 dark:text-gray-400">Décaissements</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">€2,100.00</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 border-l-4 border-blue-500">
                <p class="text-sm text-gray-600 dark:text-gray-400">Solde</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">€3,150.00</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Libellé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">09:00</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Encaissement</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Règlement client CL-001</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">+€2,500.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">€2,500.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

