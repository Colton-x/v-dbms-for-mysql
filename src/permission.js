import router from './router'
import store from './store'
import { Message } from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import { getToken } from '@/utils/auth' // get token from cookie
import getPageTitle from '@/utils/get-page-title'
import { getDbs } from '@/api/table'

import axios from 'axios'
import Layout from '@/layout' //Layout 是架构组件，不在后台返回，在文件里单独引入
const _import = require('./router/_import_' + process.env.NODE_ENV)//获取组件的方法

var getRouter

NProgress.configure({ showSpinner: false }) // NProgress Configuration

const whiteList = ['/login'] // no redirect whitelist

router.beforeEach(async(to, from, next) => {

  // start progress bar
  NProgress.start()

  // set page title
  document.title = getPageTitle(to.meta.title)

  // determine whether the user has logged in
  const hasToken = getToken()

  if (hasToken) {
    if (to.path === '/login') {
      // if is logged in, redirect to the home page
      next({ path: '/' })
      NProgress.done()
    } else {
      const hasGetUserInfo = store.getters.name
      if (hasGetUserInfo) {
        next()
      } else {
        try {
          // get user info
          await store.dispatch('user/getInfo')

          next()
        } catch (error) {
          // remove token and go to login page to re-login
          await store.dispatch('user/resetToken')
          Message.error(error || 'Has Error')
          next(`/login?redirect=${to.path}`)
          NProgress.done()
        }
      }
    }
  } else {
    /* has no token*/

    if (whiteList.indexOf(to.path) !== -1) {
      // in the free login whitelist, go directly
      next()
    } else {
      // other pages that do not have permission to access are redirected to the login page.
      next(`/login?redirect=${to.path}`)
      NProgress.done()
    }
  }

  if (!getRouter&&hasToken) {//不加这个判断，路由会陷入死循环
    if (!getObjArr('router')) {
      // console.log('getObjArr')
      // axios.get('http://localhost:8080/dbms.php?ac=getdbs').then(res => {
      //   getRouter = res.data.data.router//后台拿到路由
      //   saveObjArr('router', getRouter) //存储路由到localStorage
      //   console.log(getRouter)
      //   routerGo(to, next)//执行路由跳转方法
      // })
      getDbs().then(res => {
        console.log(res.data)
        if (res.code == 20000) {
          getRouter = res.data.router//后台拿到路由
          saveObjArr('router', getRouter) //存储路由到localStorage
          console.log(getRouter)
          routerGo(to, next)//执行路由跳转方法
          // vm.$message.success(res.message)
        } else {
          // vm.$message.error(res.message)
        }
      }).catch(function(error) {
        console.log(error)
      })
    } else {//从localStorage拿到了路由
      getRouter = getObjArr('router')//拿到路由
      routerGo(to, next)
    }
    console.log('!getRouter')
  } else {
    next()
  }
})

router.afterEach(() => {
  // finish progress bar
  NProgress.done()
  // console.log(router.options.routes)
})


function routerGo(to, next) {
  getRouter = filterAsyncRouter(getRouter) //过滤路由
  router.addRoutes(getRouter) //动态添加路由
  // global.antRouter = getRouter //将路由数据传递给全局变量，做侧边栏菜单渲染工作
  router.options.routes = getRouter
  // router.options.routes = router.options.routes.concat(getRouter)
  next({ ...to, replace: true })
}

function saveObjArr(name, data) { //localStorage 存储数组对象的方法
  localStorage.setItem(name, JSON.stringify(data))
}

function getObjArr(name) { //localStorage 获取数组对象的方法
  return JSON.parse(window.localStorage.getItem(name));

}

function filterAsyncRouter(asyncRouterMap) { //遍历后台传来的路由字符串，转换为组件对象
  const accessedRouters = asyncRouterMap.filter(route => {
    if (route.component) {
      if (route.component === 'Layout') {//Layout组件特殊处理
        route.component = Layout
      } else {
        route.component = _import(route.component)
      }
    }
    if (route.children && route.children.length) {
      route.children = filterAsyncRouter(route.children)
    }
    return true
  })

  return accessedRouters
}
