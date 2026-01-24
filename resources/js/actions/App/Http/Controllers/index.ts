import Auth from './Auth'
import ExportController from './ExportController'
import Api from './Api'
import TransactionController from './TransactionController'
import FileAccessController from './FileAccessController'
import CategoryController from './CategoryController'
import UserController from './UserController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
ExportController: Object.assign(ExportController, ExportController),
Api: Object.assign(Api, Api),
TransactionController: Object.assign(TransactionController, TransactionController),
FileAccessController: Object.assign(FileAccessController, FileAccessController),
CategoryController: Object.assign(CategoryController, CategoryController),
UserController: Object.assign(UserController, UserController),
}

export default Controllers