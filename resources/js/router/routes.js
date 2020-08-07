import layouts from './layouts'

const routes = [{
    path: '/',
    component: resolve => require(['../layout'], resolve),
    children: layouts
}];

export default routes
