 /* eslint-disable */
<template>
  <div>
    <el-card class="box-card" style="margin: 15px">
      <el-row>
        <el-button type="primary" plain icon="el-icon-plus" @click="addField">添加字段</el-button>
      </el-row>
      <el-table
        stripe
        border
        :data="tableData"
        style="width: 100%;margin-top: 20px"
      >
        <el-table-column v-for="tkey in t_column" :prop="tkey.prop" :label="tkey.label" :show-overflow-tooltip="true" />
        <el-table-column label="操作" width="120">
          <template slot-scope="scope">
            <el-button type="primary" icon="el-icon-edit" circle @click="handleEdit(scope.$index, scope.row)" />
            <el-button type="danger" icon="el-icon-delete" circle @click="handleDelete(scope.$index, scope.row)" />
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog :title="boxTitle" :visible.sync="dialogFormVisible">
      <el-form ref="ruleForm" :model="ruleForm" size="small" :rules="rules">
        <el-form-item label="字段名" :label-width="formLabelWidth" prop="name">
          <el-input v-model="ruleForm.name" autocomplete="off" />
        </el-form-item>
        <el-form-item label="类型" :label-width="formLabelWidth" prop="fieldType">
          <el-select v-model="ruleForm.fieldType" placeholder="请选择字段类型" filterable>
            <el-option label="bigint" value="bigint" />
            <el-option label="binary" value="binary" />
            <el-option label="bit" value="bit" />
            <el-option label="blob" value="blob" />
            <el-option label="char" value="char" />
            <el-option label="date" value="date" />
            <el-option label="datetime" value="datetime" />
            <el-option label="decimal" value="decimal" />
            <el-option label="double" value="double" />
            <el-option label="enum" value="enum" />
            <el-option label="float" value="float" />
            <el-option label="geometry" value="geometry" />
            <el-option label="geometrycollection" value="geometrycollection" />
            <el-option label="int" value="int" />
            <el-option label="integer" value="integer" />
            <el-option label="json" value="json" />
            <el-option label="linestring" value="linestring" />
            <el-option label="longblob" value="longblob" />
            <el-option label="longtext" value="longtext" />
            <el-option label="mediumblob" value="mediumblob" />
            <el-option label="mediumint" value="mediumint" />
            <el-option label="mediumtext" value="mediumtext" />
            <el-option label="multilinestring" value="multilinestring" />
            <el-option label="multipoint" value="multipoint" />
            <el-option label="multipolygon" value="multipolygon" />
            <el-option label="numeric" value="numeric" />
            <el-option label="point" value="point" />
            <el-option label="polygon" value="polygon" />
            <el-option label="real" value="real" />
            <el-option label="set" value="set" />
            <el-option label="text" value="text" />
            <el-option label="time" value="time" />
            <el-option label="timestamp" value="timestamp" />
            <el-option label="tinyblob" value="tinyblob" />
            <el-option label="tinyint" value="tinyint" />
            <el-option label="tinytext" value="tinytext" />
            <el-option label="varbinary" value="varbinary" />
            <el-option label="varchar" value="varchar" />
            <el-option label="year" value="year" />
          </el-select>
        </el-form-item>
        <el-form-item label="长度" :label-width="formLabelWidth">
          <el-input v-model="ruleForm.fieldLen" autocomplete="off" />
        </el-form-item>
        <el-form-item label="是否为空" :label-width="formLabelWidth">
          <el-switch v-model="ruleForm.isnull" />
          <!-- <el-radio v-model="ruleForm.isnull" label="1" border>为空</el-radio> -->
          <!-- <el-radio v-model="ruleForm.isnull" label="2" border>不为空</el-radio> -->
        </el-form-item>
        <el-form-item label="自动递增" :label-width="formLabelWidth">
          <!-- <el-radio v-model="ruleForm.auto_increment" label="1" border>自动递增</el-radio> -->
          <!-- <el-radio v-model="ruleForm.auto_increment" label="2" border>不自动递增</el-radio> -->
          <el-switch v-model="ruleForm.auto_increment" />
        </el-form-item>
        <el-form-item label="是否主键" :label-width="formLabelWidth">
          <!-- <el-radio v-model="ruleForm.pri" label="1" border>主键</el-radio>
            <el-radio v-model="ruleForm.pri" label="2" border>不是主键</el-radio> -->
          <el-switch v-model="ruleForm.pri" />
        </el-form-item>
        <el-form-item label="默认值" :label-width="formLabelWidth">
          <el-input v-model="ruleForm.default_v" autocomplete="off" />
        </el-form-item>
        <el-form-item label="注释" :label-width="formLabelWidth">
          <el-input v-model="ruleForm.desc" autocomplete="off" />
        </el-form-item>

        <el-form-item label="字符集" :label-width="formLabelWidth">
          <el-select v-model="ruleForm.char" placeholder="请选择字符集">
            <el-option label="utf8" value="utf8" />
            <el-option label="utf8mb4" value="utf8mb4" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序规则" :label-width="formLabelWidth">
          <el-select v-model="ruleForm.collationName" placeholder="请选择排序规则">
            <el-option label="utf8_general_ci" value="utf8_general_ci" />
            <el-option label="utf8mb4_general_ci" value="utf8mb4_general_ci" />
            <el-option label="utf8mb4_unicode_ci" value="utf8mb4_unicode_ci" />
          </el-select>
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="resetForm('ruleForm')">取 消</el-button>
        <el-button type="primary" :loading="sendData" @click="submitForm('ruleForm')">确 定</el-button>
      </div>
    </el-dialog>
  </div>

</template>

<script>
import { getStructure, delField, addField } from '@/api/table'
export default {
  name: 'Design',
  data() {
    return {
      db: '',
      tb: '',
      t_column: [
        { prop: 'COLUMN_NAME', label: '名称' },
        { prop: 'DATA_TYPE', label: '类型' },
        { prop: 'CHARACTER_MAXIMUM_LENGTH', label: '长度' },
        { prop: 'IS_NULLABLE', label: '是否为空' },
        { prop: 'COLUMN_KEY', label: '键' },
        { prop: 'EXTRA', label: '自动递增' },
        { prop: 'COLUMN_DEFAULT', label: '默认值' },
        { prop: 'CHARACTER_SET_NAME', label: '字符集' },
        { prop: 'COLLATION_NAME', label: '排序规则' },
        { prop: 'COLUMN_COMMENT', label: '注释' }
      ],
      tableData: [],
      dialogFormVisible: false,
      ruleForm: {
        name: '',
        fieldLen: '',
        fieldType: '',
        isnull: false,
        auto_increment: false,
        pri: false,
        default_v: '',
        desc: '',
        char: '',
        collationName: '',
        type: []
      },
      formLabelWidth: '120px',
      boxTitle: '添加字段',
      sendData: false,
      rules: {
        name: [
          { required: true, message: '请输入字段名', trigger: 'blur' },
          { min: 1, max: 15, message: '长度在 1 到 15 个字符', trigger: 'blur' }
        ],
        fieldType: [
          { required: true, message: '请选择字段类型', trigger: 'change' }
        ]
      }
    }
  },
  created() {
    // console.log(this.$route.query)
    this.tb = this.$route.query.tb
    this.db = this.$route.query.db
    const postData = { 'db': this.db, 'tb': this.tb }
    getStructure(postData).then(res => {
      this.tableData = res.data
    }).catch(function(error) {
      console.log(error)
    })
  },
  methods: {
    handleEdit(index, row) {
      console.log(index, row)
      this.boxTitle = '编辑字段'
      this.dialogFormVisible = true
      this.ruleForm.name = row.COLUMN_NAME
      this.ruleForm.fieldLen = row.CHARACTER_MAXIMUM_LENGTH
      this.ruleForm.fieldType = row.DATA_TYPE
      this.ruleForm.isnull = (row.IS_NULLABLE == 'YES')
      this.ruleForm.auto_increment = (row.EXTRA == 'auto_increment')
      this.ruleForm.pri = (row.COLUMN_KEY == 'PRI')
      this.ruleForm.default_v = row.COLUMN_DEFAULT
      this.ruleForm.desc = row.COLUMN_COMMENT
      this.ruleForm.char = row.CHARACTER_SET_NAME
      this.ruleForm.collationName = row.COLLATION_NAME
    },
    handleDelete(index, row) {
      // let vm = this
      console.log(index, row)
      const postData = { 'db': this.db, 'tb': this.tb, 'fieldName': row.COLUMN_NAME }
      delField(postData).then(res => {
        this.tableData.splice(index, 1)
        this.$message({
          message: '已删除',
          type: 'success'
        })
      }).catch(function(error) {
        console.log(error)
      })
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.sendData = true

          const postData = { 'db': this.db, 'tb': this.tb, 'fieldName': this.ruleForm.name, 'fieldType': this.ruleForm.fieldType }
          addField(postData).then(res => {
            this.sendData = false
            this.dialogFormVisible = false

            getStructure(postData).then(res => {
              this.tableData = res.data
            }).catch(function(error) {
              console.log(error)
            })

            this.$message({
              message: '已添加',
              type: 'success'
            })
          }).catch(function(error) {
            console.log(error)
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
      this.dialogFormVisible = false
    },
    addField() {
      this.boxTitle = '添加字段'
      this.dialogFormVisible = true
    }
  }
}
</script>
