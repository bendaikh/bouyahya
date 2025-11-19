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
