import auth_routes from './auth'
import layouts from './layouts'

const routes = auth_routes.concat(layouts);

export default routes
