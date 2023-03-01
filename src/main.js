import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import './style.css'
import App from './App.vue'
import 'vuetify/styles'
import 'material-design-icons-iconfont/dist/material-design-icons.css'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import HomePage from './components/HomePage.vue'  
import Settings from './components/Settings.vue'
import Users from './components/Users.vue'
import Student from '@/components/Student.vue'

const vuetify = createVuetify({
    components,
    directives,
})


const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        { path: '/', name: 'Home', component: HomePage },
        { path: '/settings', name: 'Settings', component: Settings },
        { path: '/student', name: 'Student', component: Student },
        { path: '/users', name: 'Users', component: Users },
    ]
})

createApp(App)
    .use(vuetify)
    .use(router)
    .mount('#app');

