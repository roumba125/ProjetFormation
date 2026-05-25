import { createApp, defineComponent, h } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory, RouterView } from 'vue-router'

import AppLayout     from './layouts/AppLayout.vue'
import Home          from './pages/Home.vue'
import Catalogue     from './pages/Catalogue.vue'
import SheepDetail   from './pages/SheepDetail.vue'
import Checkout      from './pages/Checkout.vue'
import OrderTracking from './pages/OrderTracking.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: AppLayout,
            children: [
                { path: '',           component: Home,          name: 'home' },
                { path: 'catalogue',  component: Catalogue,     name: 'catalogue' },
                { path: 'mouton/:id', component: SheepDetail,   name: 'sheep.show' },
                { path: 'commander',  component: Checkout,      name: 'checkout' },
                { path: 'suivi',      component: OrderTracking, name: 'order.track' },
            ],
        },
        { path: '/:any(.*)', redirect: '/' },
    ],
    scrollBehavior: () => ({ top: 0 }),
})

const pinia = createPinia()

const Root = defineComponent({
    setup() { return () => h(RouterView) },
})

const app = createApp(Root)
app.use(pinia)
app.use(router)
app.mount('#app')
