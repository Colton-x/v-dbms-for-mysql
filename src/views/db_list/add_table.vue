<template>
  <div>
    <el-card class="box-card" style="margin: 15px">
      <vxe-toolbar>
        <template v-slot:buttons>
          <!-- <vxe-button @click="$refs.xTable.insert({name: Date.now()})">首行插入</vxe-button> -->
          <vxe-button @click="addline">插入字段</vxe-button>
          <vxe-button @click="$refs.xTable.removeSelecteds()">删除选中</vxe-button>
          <vxe-input v-model="tbName" placeholder="表名" />
          <vxe-input v-model="tbComment" placeholder="表注释" />
          <vxe-button @click="saveTable">保存</vxe-button>
          <!-- <vxe-button @click="getInsertEvent">获取新增</vxe-button> -->
        </template>
      </vxe-toolbar>

      <vxe-table
        ref="xTable"
        border
        show-overflow
        row-id="id"
        :data.sync="tableData"
        :edit-config="{trigger: 'click', mode: 'cell'}"
      >
        <vxe-table-column type="selection" width="60" />
        <vxe-table-column type="index" width="60" title="序号" />
        <vxe-table-column field="COLUMN_NAME" title="名称" :edit-render="{name: 'input'}" />
        <vxe-table-column field="DATA_TYPE" title="类型" :edit-render="{name: 'ElSelect', options: typeList}" />
        <vxe-table-column field="CHARACTER_MAXIMUM_LENGTH" title="长度" width="200" :edit-render="{name: 'ElInputNumber', props: { min: 1}}" />
        <vxe-table-column field="IS_NULLABLE" title="不为空" width="100" :edit-render="{name: 'ElSwitch', type: 'visible'}" />
        <vxe-table-column field="COLUMN_KEY" title="主键" width="100" :edit-render="{name: 'ElSwitch', type: 'visible'}" />
        <vxe-table-column field="EXTRA" title="自动递增" width="100" :edit-render="{name: 'ElSwitch', type: 'visible'}" />
        <vxe-table-column field="COLUMN_DEFAULT" title="默认值" :edit-render="{name: 'input'}" />
        <vxe-table-column field="CHARACTER_SET_NAME" title="字符集" :edit-render="{name: 'ElSelect', options: charType}" />
        <vxe-table-column field="COLLATION_NAME" title="排序规则" :edit-render="{name: 'ElSelect', options: colla}" />
        <vxe-table-column field="COLUMN_COMMENT" title="注释" :edit-render="{name: 'input'}" />
        <template v-slot:empty>
          <span>请插入字段</span>
        </template>
      </vxe-table>
    </el-card>
  </div>
</template>

<script>
// import router from '../../router'
import { addTable, getDbs } from '@/api/table'
export default {
  data() {
    return {
      tbName: '',
      tbComment: '',
      db: '',
      tableData: [],
      tid: 0,
      charType: [
        {
          'label': 'utf8',
          'value': 'utf8'
        },
        {
          'label': 'utf8mb4',
          'value': 'utf8mb4'
        }
      ],
      colla: [
        {
          'label': 'utf8_general_ci',
          'value': 'utf8_general_ci'
        },
        {
          'label': 'utf8mb4_general_ci',
          'value': 'utf8mb4_general_ci'
        },
        {
          'label': 'utf8mb4_unicode_ci',
          'value': 'utf8mb4_unicode_ci'
        }
      ],
      typeList: [
        {
          'label': 'bigint',
          'value': 'bigint'
        },
        {
          'label': 'binary',
          'value': 'binary'
        },
        {
          'label': 'bit',
          'value': 'bit'
        },
        {
          'label': 'blob',
          'value': 'blob'
        },
        {
          'label': 'char',
          'value': 'char'
        },
        {
          'label': 'date',
          'value': 'date'
        },
        {
          'label': 'datetime',
          'value': 'datetime'
        },
        {
          'label': 'decimal',
          'value': 'decimal'
        },
        {
          'label': 'double',
          'value': 'double'
        },
        {
          'label': 'enum',
          'value': 'enum'
        },
        {
          'label': 'float',
          'value': 'float'
        },
        {
          'label': 'geometry',
          'value': 'geometry'
        },
        {
          'label': 'geometrycollection',
          'value': 'geometrycollection'
        },
        {
          'label': 'int',
          'value': 'int'
        },
        {
          'label': 'integer',
          'value': 'integer'
        },
        {
          'label': 'json',
          'value': 'json'
        },
        {
          'label': 'linestring',
          'value': 'linestring'
        },
        {
          'label': 'longblob',
          'value': 'longblob'
        },
        {
          'label': 'longtext',
          'value': 'longtext'
        },
        {
          'label': 'mediumblob',
          'value': 'mediumblob'
        },
        {
          'label': 'mediumint',
          'value': 'mediumint'
        },
        {
          'label': 'mediumtext',
          'value': 'mediumtext'
        },
        {
          'label': 'multilinestring',
          'value': 'multilinestring'
        },
        {
          'label': 'multipoint',
          'value': 'multipoint'
        },
        {
          'label': 'multipolygon',
          'value': 'multipolygon'
        },
        {
          'label': 'numeric',
          'value': 'numeric'
        },
        {
          'label': 'point',
          'value': 'point'
        },
        {
          'label': 'polygon',
          'value': 'polygon'
        },
        {
          'label': 'real',
          'value': 'real'
        },
        {
          'label': 'set',
          'value': 'set'
        },
        {
          'label': 'text',
          'value': 'text'
        },
        {
          'label': 'time',
          'value': 'time'
        },
        {
          'label': 'timestamp',
          'value': 'timestamp'
        },
        {
          'label': 'tinyblob',
          'value': 'tinyblob'
        },
        {
          'label': 'tinyint',
          'value': 'tinyint'
        },
        {
          'label': 'tinytext',
          'value': 'tinytext'
        },
        {
          'label': 'varbinary',
          'value': 'varbinary'
        },
        {
          'label': 'varchar',
          'value': 'varchar'
        },
        {
          'label': 'year',
          'value': 'year'
        }
      ]
    }
  },
  created() {
    this.db = this.$route.query.db
  },
  methods: {
    addline() {
      this.tid += 1
      this.$refs.xTable.insertAt({ id: this.tid }, -1).then(({ row }) => this.$refs.xTable.setActiveCell(row, 'COLUMN_NAME'))
    },
    saveTable() {
      console.log(this.$router.options.routes)
      const vm = this
      const dataFields = this.$refs.xTable.getInsertRecords()
      if (!this.tbName) {
        this.$message.warning('请输入表名')
        return false
      }
      if (dataFields.length < 1) {
        this.$message.warning('请添加字段')
        return false
      }

      const postData = { 'db': this.db, 'tb': this.tbName, 'fields': dataFields, 'comment': this.tbComment }
      addTable(postData).then(res => {
        console.log(res.data)
        if (res.code == 20000) {
          vm.$message.success(res.message)
          localStorage.setItem('router', JSON.stringify(res.data))
        } else {
          vm.$message.error(res.message)
        }
      }).catch(function(error) {
        console.log(error)
      })
    },
    getInsertEvent() {
      const insertRecords = this.$refs.xTable.getInsertRecords()
      this.$XMsg.alert(insertRecords.length)
      console.log(insertRecords)
    }
  }
}
</script>
