import request from '@/utils/request'

// export function login(data) {
//   return request({
//     url: '/user/login',
//     method: 'post',
//     data
//   })
// }

// export function getInfo(token) {
//   return request({
//     url: '/user/info',
//     method: 'get',
//     params: { token }
//   })
// }

// export function logout() {
//   return request({
//     url: '/user/logout',
//     method: 'post'
//   })
// }

export function login(data) {
  return request({
    url: '&ac=login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: '&ac=info',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return request({
    url: '&ac=logout',
    method: 'post'
  })
}