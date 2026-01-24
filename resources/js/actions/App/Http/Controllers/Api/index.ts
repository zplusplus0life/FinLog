import AuthController from './AuthController'
import DashboardController from './DashboardController'
import TransactionController from './TransactionController'
const Api = {
    AuthController: Object.assign(AuthController, AuthController),
DashboardController: Object.assign(DashboardController, DashboardController),
TransactionController: Object.assign(TransactionController, TransactionController),
}

export default Api