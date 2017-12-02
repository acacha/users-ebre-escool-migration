// Register components
Vue.component('users-migration-dashboard', require('./components/UsersMigrationDashboardComponent.vue'))
Vue.component('users-migration', require('./components/UsersMigrationComponent.vue'))

import { config } from './config/ebre-escool-users-migration'

window.ebre_escool_users_migration = {}
window.ebre_escool_users_migration.config = config