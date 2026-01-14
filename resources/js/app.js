import './bootstrap';
import { createApp } from 'vue';

// Import Vue components
import ExampleComponent from './components/ExampleComponent.vue';
import CounterComponent from './components/CounterComponent.vue';
import TodoListComponent from './components/TodoListComponent.vue';
import Sidebar from './components/Sidebar.vue';
import MenuItem from './components/MenuItem.vue';
import CollapsibleMenuItem from './components/CollapsibleMenuItem.vue';
import ThemeToggle from './components/ThemeToggle.vue';
import App from './App.vue';
import CreateClient from './components/clients/CreateClient.vue';
import ClientsList from './components/clients/ClientsList.vue';
import CreateFournisseur from './components/fournisseurs/CreateFournisseur.vue';
import FournisseursList from './components/fournisseurs/FournisseursList.vue';
import Settings from './components/Settings.vue';
import SettingsApplication from './components/SettingsApplication.vue';
import SettingsVilles from './components/SettingsVilles.vue';
import SettingsArticles from './components/SettingsArticles.vue';
import SettingsCommerciales from './components/SettingsCommerciales.vue';
import SettingsTransports from './components/SettingsTransports.vue';
import SettingsMatricules from './components/SettingsMatricules.vue';
import SettingsBanques from './components/SettingsBanques.vue';
import SettingsTypesReglement from './components/SettingsTypesReglement.vue';
import SettingsEcheances from './components/SettingsEcheances.vue';
import BonAchatFournisseur from './components/achats/BonAchatFournisseur.vue';
import ReglementsFournisseurs from './components/achats/ReglementsFournisseurs.vue';
import HistoriqueAchats from './components/achats/HistoriqueAchats.vue';
import ReleveCompteFournisseurs from './components/achats/ReleveCompteFournisseurs.vue';
import ArticlesList from './components/stock/ArticlesList.vue';
import ReglementsClients from './components/ventes/ReglementsClients.vue';
import HistoriqueVentes from './components/ventes/HistoriqueVentes.vue';
import ReleveCompteClients from './components/ventes/ReleveCompteClients.vue';
import EcheancierFournisseurs from './components/achats/EcheancierFournisseurs.vue';
import TypesCharges from './components/tresorerie/TypesCharges.vue';
import UsersList from './components/utilisateurs/UsersList.vue';
import RolesList from './components/utilisateurs/RolesList.vue';

// Initialize Vue app with root component
const app = createApp(App);

// Register components globally
app.component('ExampleComponent', ExampleComponent);
app.component('CounterComponent', CounterComponent);
app.component('TodoListComponent', TodoListComponent);
app.component('Sidebar', Sidebar);
app.component('MenuItem', MenuItem);
app.component('CollapsibleMenuItem', CollapsibleMenuItem);
app.component('ThemeToggle', ThemeToggle);
app.component('CreateClient', CreateClient);
app.component('ClientsList', ClientsList);
app.component('CreateFournisseur', CreateFournisseur);
app.component('FournisseursList', FournisseursList);
app.component('Settings', Settings);
app.component('SettingsApplication', SettingsApplication);
app.component('SettingsVilles', SettingsVilles);
app.component('SettingsArticles', SettingsArticles);
app.component('SettingsCommerciales', SettingsCommerciales);
app.component('SettingsTransports', SettingsTransports);
app.component('SettingsMatricules', SettingsMatricules);
app.component('SettingsBanques', SettingsBanques);
app.component('SettingsTypesReglement', SettingsTypesReglement);
app.component('SettingsEcheances', SettingsEcheances);
app.component('BonAchatFournisseur', BonAchatFournisseur);
app.component('ReglementsFournisseurs', ReglementsFournisseurs);
app.component('HistoriqueAchats', HistoriqueAchats);
app.component('ReleveCompteFournisseurs', ReleveCompteFournisseurs);
app.component('ArticlesList', ArticlesList);
app.component('ReglementsClients', ReglementsClients);
app.component('HistoriqueVentes', HistoriqueVentes);
app.component('ReleveCompteClients', ReleveCompteClients);
app.component('EcheancierFournisseurs', EcheancierFournisseurs);
app.component('TypesCharges', TypesCharges);
app.component('UsersList', UsersList);
app.component('RolesList', RolesList);

// Mount Vue app to elements with id="app"
document.addEventListener('DOMContentLoaded', () => {
    const vueApp = document.getElementById('app');
    if (vueApp) {
        // Get the content from Blade before Vue replaces it
        const bladeContent = vueApp.innerHTML.trim();
        const pageComponentName = vueApp.dataset.pageComponent;

        // Mount the app
        app.mount('#app');

        // Insert Blade content into the slot after Vue renders
        if (!pageComponentName && bladeContent) {
            setTimeout(() => {
                const slotContent = document.querySelector('#main-content main');
                if (slotContent) {
                    slotContent.innerHTML = bladeContent;
                }
            }, 0);
        }
    }
});
