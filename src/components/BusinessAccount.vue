<template>
  <div class="container-fluid" >
    <div class="row my-3">
      <div class="col-5">

      </div>
      <div class="col-5">

      </div>
      <div class="col-2">
        <el-input @change="getAccounts" v-model="search" size="small" :placeholder="Search" />
      </div>
    </div>
    <div class="row">
      <!-- Product Table -->
      <div class="col-12">
        <h5>Business Account Requests</h5>
        <el-table ref="productsTable" :data="accounts" :max-height="680" style="width: 100%" v-loading="tableLoading">

          <el-table-column label="ID" prop="id" />
          <el-table-column label="Pass" prop="pass" />
          <el-table-column label="Name" prop="name" />
          <el-table-column label="Email" prop="email" />
          <el-table-column label="Phone" prop="phone" />
          <el-table-column label="Company" prop="company" />
          <el-table-column label="Business Type" prop="business_type" />
          <el-table-column label="Address" prop="address" >
            <template #default="scope">
              <el-tooltip
                  class="box-item"
                  effect="dark"
                  :content="scope.row.address"
                  placement="top-start"
              >
                <span :value="scope.row.address" >{{scope.row.address.substring(0, 20) + '...'}}</span>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column label="Description" prop="description" >
            <template #default="scope">
              <el-tooltip
                  class="box-item"
                  effect="dark"
                  :content="scope.row.description"
                  placement="top-start"
              >
                <span :value="scope.row.status" >{{scope.row.description.substring(0, 20) + '...'}}</span>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column label="Status" width="85" prop="status" >
            <template #default="scope">
              <el-badge  v-if="scope.row.status === 'Approved'" type="primary" :value="scope.row.status" ></el-badge>
              <el-badge  v-if="scope.row.status === 'Pending'" type="warning" :value="scope.row.status" ></el-badge>
              <el-badge  v-if="scope.row.status === 'Denied'" type="danger" :value="scope.row.status" ></el-badge>
            </template>
          </el-table-column>

          <el-table-column align="right" label="Actions">
            <template #default="scope">
              <el-button  size="small" type="primary" plain @click="goAction('approve', scope.row.id, scope.row.email)">
                 <el-icon><Check /></el-icon> Approve
              </el-button>
              <el-button size="small"  type="danger" plain @click="goAction( 'deny', scope.row.id, scope.row.email)">
                 <el-icon><CloseBold /></el-icon> Deny
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col-3">
        <span class="pagination-footer-count-text">{{ this.showing() }}</span>
      </div>
      <div class="col-6">
        <div class="product-footer-pagination">
          <el-pagination
              layout="prev, pager, next"
              v-model:current-page="currentPage"
              :page-size="length"
              :total="accountTotal"
              @current-change="changePage"
          />
        </div>
      </div>
      <div class="col-3">
        <el-select size="small" class="page-length" @change="getAccounts" v-model="length" placeholder="Select">
          <el-option :key="2" :value="2" label="2" />
          <el-option :key="10" :value="10" label="10" />
          <el-option :key="15" :value="15" label="15" />
          <el-option :key="20" :value="20" label="20" />
        </el-select>
      </div>
    </div>
  </div>
</template>

<script>
const axios = require('axios');
import { ElMessage } from 'element-plus';
import { View,Edit, Check, CloseBold } from '@element-plus/icons-vue';
import $ from "jquery";

export default {
  components:{
    Edit,View, Check, CloseBold
  },
  data() {
    return {
      search: '',
      length: 10,
      accounts:[],
      accountTotal: 0,
      currentPage: 1,
      tableLoading: false,
      ajax_url: es_parameters.ajax_url,
    }
  },
  computed:{

  },
  created(){
    this.getAccounts();
  },
  methods:{
    getAccounts(){
      this.tableLoading = true;
      const formData = new FormData();
      formData.append('action', 'get_business_account');
      formData.append('start', (this.currentPage -1) * this.length);
      formData.append('length', this.length);
      formData.append('search', this.search);

      axios.post(this.ajax_url, formData)
          .then( response => {
            if(response.data.success){
              this.accounts = response.data.users;
              this.accountTotal = response.data.recordsFiltered;
            }else{
              ElMessage.error('')
            }

            this.tableLoading = false;
          })
          .catch( error => {
            ElMessage.error(error.message);
            this.tableLoading = false;
          });
    },
    changePage(){
      this.getAccounts();
    },
    showing(){
      var length = this.accountTotal < this.length ? this.accountTotal : this.length;

      return "Showing" + " " + length + ' of ' + this.accountTotal + ' accounts';
    },

    changeLoading(flag){
      this.$emit('changeLoading',flag)
    },
    goAction(action, user_id, email){
      this.tableLoading = true;
      const formData = new FormData();
      formData.append('action', 'es_approve_deny_business');
      formData.append('action_account', action);
      formData.append('user_id', user_id);
      formData.append(email, email);

      axios.post(this.ajax_url, formData)
          .then( response => {
            if(response.data.success){
              this.getAccounts();
            }else{
              ElMessage.error(response.data.msg)
            }

            this.tableLoading = false;
          })
          .catch( error => {
            ElMessage.error('There was na error, please try later');
            this.tableLoading = false;
          });
    }
  }
}
</script>

<style>

.el-table_1_column_7 .cell{
  text-align: center;
}

.el-input__wrapper{
  padding: 0 !important;
}
th .cell{
  font-size: 11px;
}
.product-footer-pagination{
  display: flex;
  justify-content: center;
  align-content: center;
}

.btn-prev, .btn-next{
  margin-top: -6px !important;
}

.el-input__inner{
  background: white !important;
}

.pagination-footer-count-text{
  font-size: 12px;
}

.el-badge__content{
  font-size: 10px !important;
}

.price-column > .cell > .el-input-number{
  width: 80px !important;
}

.page-length{
  width: 65px;
  float: right;
}

.show-icon svg{
  height: 14px !important;
}

.price-list-select{
  width: 280px;
}

</style>
