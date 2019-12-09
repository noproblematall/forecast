import VueRouter from 'vue-router';
import Vue from 'vue';
const Wave = () => import(/* webpackChunkName: "wave" */'./components/Wave')
const Wind = () => import(/* webpackChunkName: "wind" */'./components/Wind')
Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path:'/',
            name: 'init_wave',
            component: Wave,
        },
        {
            path: '/wind',
            name: 'init_wind',
            component: Wind,
        },
        {
            path:'/:location/:date',
            name: 'wave',
            component: Wave,
            props: true,
        },
        {
            path: '/wind/:location/:date',
            name: 'wind',
            component: Wind,
            props: true,
        },

        {
            path: '*',
            redirect: '/',
        }
    ]
});

export default router;
