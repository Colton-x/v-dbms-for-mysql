<template>
  <el-card class="box-card" style="margin: 15px">
    <el-row>
      <el-button type="primary" plain icon="el-icon-plus" @click="addTable">新建表</el-button>
      <el-button type="primary" plain icon="el-icon-setting" @click="designTable">设计表</el-button>
      <el-button type="danger" plain icon="el-icon-delete">删除表</el-button>
      <el-button type="danger" plain icon="el-icon-close">清空表</el-button>
      <el-button type="primary" plain icon="el-icon-download">导出表</el-button>
      <el-button type="primary" plain icon="el-icon-download">转储SQL文件</el-button>
      <el-button type="primary" plain icon="el-icon-circle-plus-outline">插入记录</el-button>
    </el-row>
    <div style="margin-top: 20px">
      <el-form :inline="true" :model="formInline" class="demo-form-inline">
        <el-form-item label="运行SQL">
          <el-input v-model="formInline.sql" placeholder="输入SQL" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-caret-right" @click="onSubmit">执行</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-table
      v-loading="loading"
      stripe
      border
      :data="tableData"
      style="width: 100%;"
      element-loading-text="拼命加载中"
      element-loading-spinner="el-icon-loading"
    >
      <el-table-column v-for="tkey in t_column" :prop="tkey.prop" :label="tkey.label" :show-overflow-tooltip="true" />
    </el-table>
    <div style="text-align: center;margin-top: 25px">
      <el-pagination
        background
        :current-page="currentPage"
        :page-sizes="[20, 40, 60, 80]"
        :page-size="page_size"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
  </el-card>
</template>

<script>
// import axios from 'axios'
import { getTables } from '@/api/table'
export default {
  name: 'Dblist',
  data() {
    return {
      loading: true,
      t_column: [],
      tableData: [],
      currentPage: 1,
      total: 0,
      page_size: 20,
      tb: '',
      db: '',
      formInline: {
        sql: ''
      }
    }
  },
  created() {
    this.tb = this.$route.meta.title
    this.db = this.$route.meta.db
    const postData = { 'db': this.db, 'tb': this.tb, 'page': this.currentPage, 'pageSize': this.page_size }
    getTables(postData).then(res => {
      this.t_column = res.data.field
      this.tableData = res.data.datas
      this.total = res.data.count
      this.loading = false
    }).catch(function(error) {
      console.log(error)
    })
  },
  methods: {
    handleSizeChange(val) {
      this.loading = true
      this.page_size = val
      const postData = { 'db': this.db, 'tb': this.tb, 'page': this.currentPage, 'pageSize': this.page_size }
      getTables(postData).then(res => {
        this.tableData = res.data.datas
        this.loading = false
      }).catch(function(error) {
        console.log(error)
      })
    },
    handleCurrentChange(val) {
      this.loading = true
      this.currentPage = val
      const postData = { 'db': this.db, 'tb': this.tb, 'page': this.currentPage, 'pageSize': this.page_size }
      getTables(postData).then(res => {
        // console.log(res)
        this.tableData = res.data.datas
        this.loading = false
      }).catch(function(error) {
        console.log(error)
      })
    },
    onSubmit() {
      console.log('submit!')
    },
    designTable() {
      this.$router.push('/db_list/design?db=' + this.db + '&tb=' + this.tb)
    },
    addTable() {
      this.$router.push('/db_list/add_table?db=' + this.db + '&tb=' + this.tb)
    }
  }
}
</script>
