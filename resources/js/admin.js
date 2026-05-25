// resources/js/admin.js
import { createApp, defineComponent, h } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory, RouterView } from 'vue-router'

import AdminLogin      from './admin/pages/Login.vue'
import AdminLayout     from './admin/layouts/AdminLayout.vue'
import Dashboard       from './admin/pages/Dashboard.vue'
import SheepList       from './admin/pages/sheep/SheepList.vue'
import SheepForm       from './admin/pages/sheep/SheepForm.vue'
import SheepDetail     from './admin/pages/sheep/SheepDetail.vue'
import OrderList       from './admin/pages/orders/OrderList.vue'
import OrderDetail     from './admin/pages/orders/OrderDetail.vue'

import { useAuthStore } from './admin/stores/auth'

const router = createRouter({
    history: createWebHistory('/admin'),
    routes: [
        {
            path: '/login',
            component: AdminLogin,
            name: 'admin.login',
            meta: { guest: true }
        },
        {
            path: '/',
            component: AdminLayout,
            meta: { requiresAuth: true },
            children: [
                { path: '',          component: Dashboard,   name: 'admin.dashboard' },
                { path: 'moutons',   component: SheepList,   name: 'admin.sheep.index' },
                { path: 'moutons/nouveau', component: SheepForm, name: 'admin.sheep.create' },
                { path: 'moutons/:id',     component: SheepDetail, name: 'admin.sheep.show' },
                { path: 'moutons/:id/modifier', component: SheepForm, name: 'admin.sheep.edit' },
                { path: 'commandes', component: OrderList,   name: 'admin.orders.index' },
                { path: 'commandes/:id', component: OrderDetail, name: 'admin.orders.show' },
            ]
        },
        { path: '/:any(.*)', redirect: '/' }
    ],
    scrollBehavior: () => ({ top: 0 }),
})

// Guard navigation
router.beforeEach(async (to) => {
    const auth = useAuthStore()
    await auth.init()

    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        return { name: 'admin.login' }
    }
    if (to.meta.guest && auth.isLoggedIn) {
        return { name: 'admin.dashboard' }
    }
})

const pinia = createPinia()

const Root = defineComponent({
    name: 'AdminRoot',
    setup() {
        return () => h(RouterView)
    }
})

const app = createApp(Root)
app.use(pinia)
app.use(router)
app.mount('#app')
