import request from '@/utils/request'

export function getList(params) {
  return request({
    url: '/table/list',
    method: 'get',
    params
  })
}

export function getTables(params) {
  return request({
    url: '&ac=getTables',
    method: 'post',
    params
  })
}

export function getStructure(params) {
  return request({
    url: '&ac=getStructure',
    method: 'post',
    params
  })
}

export function delField(params) {
  return request({
    url: '&ac=delField',
    method: 'post',
    params
  })
}

export function addField(params) {
  return request({
    url: '&ac=addField',
    method: 'post',
    params
  })
}

export function addTable(params) {
  return request({
    url: '&ac=addTable',
    method: 'post',
    params
  })
}

export function getDbs() {
  return request({
    url: '&ac=getDbs',
    method: 'get'
  })
}
