<template>

  <el-col class="container-fluid wrpl-admin-main p-0 m-0" v-loading="loading">
    <el-row class="tac">
      <el-col :span="4" class="wrpl-sidebar">
<!--        <el-image
            style="width: 100%"
            :src="plugin_url + '/assets/images/logo.png'"
            :initial-index="4"
            fit="contain"
        />
        <hr>-->
        <h5 class="es_sidebar_heading">Earth Savers </h5>
        <hr>
        <el-menu
            default-active="1"
            class="el-menu-vertical-main"
        >

          <el-menu-item index="1" @click="changeScreen('accounts')">
            <el-icon><user-filled /></el-icon>
            <span>Business Accounts</span>
          </el-menu-item>
          <el-menu-item index="2" @click="changeScreen('price_lists')">
            <el-icon><setting /></el-icon>
            <span>Settings</span>
          </el-menu-item>

        </el-menu>
      </el-col>
      <el-col :span="20" class="main-area">

        <BusinessAccount v-if="active_screen === 'accounts'" @changeLoading="changeLoading"></BusinessAccount>

      </el-col>
    </el-row>
  </el-col>
</template>

<script>
const axios = require('axios');
import { Setting, UserFilled } from '@element-plus/icons-vue';
import BusinessAccount from "./components/BusinessAccount.vue";

export default {
  components:{
    Setting, UserFilled, BusinessAccount
  },
  data() {
    return {
      loading: false,
      plugin_url: es_parameters.plugin_url,
      ajax_url: es_parameters.ajax_url,
      active_screen: 'accounts',
      valid: false,
    }
  },
  computed:{

  },
  created(){

  },
  methods:{
    changeScreen(screen){
      this.active_screen = screen;
    },
    changeLoading(flag){
      this.loading = flag;
    },
  }
}
</script>

<style>
#wrpl-app{
  background: #eef1f5 !important;
  height: 100vh;
}

.wrpl-sidebar, .wrpl-admin-main, .wrpl-admin-main > .el-row, .wrpl-sidebar > .el-menu{
  height: 100%;
}

.el-table__body, #wrpl-app{
  font-family: 'Poppins' !important;

}

.el-table__body .cell{
  font-size: 11px;
}

#wpfooter{
  display: none;
}
#wpbody-content{
  padding-bottom: 0 !important;
}

.wrpl-sidebar{
  background: white;
}

.es_sidebar_heading{
  margin-left: 16px;
  margin-top: 16px;
}

.wrpl-sidebar hr{
  margin: 0 !important;
}

.wrpl-sidebar span{
  font-size: 13px;
}

.wrpl-sidebar .el-menu{
  border: 0;
}


</style>
