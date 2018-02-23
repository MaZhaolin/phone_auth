<template>
  <div class="user-management">
    <div class="management-title">用户管理
      <div class="right">
        <div class="search">
          <input type="text" class="input" @keydown.enter="searchHandle" v-model="form.value">
          <vp-select v-model="form.key">
            <vp-option value="phone">手机号</vp-option>
            <vp-option value="uid">ID</vp-option>
            <vp-option value="username">用户名</vp-option>
          </vp-select>
          <button class="btn btn-search" @click="searchHandle">
              <i class="iconfont">&#xe6d4;</i>
          </button>
        </div>
        <!-- <button class="btn btn-download">下载数据</button> -->
      </div>
    </div>
    <div class="table">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>用户名</th>
            <th>邮箱</th>
            <th>手机号</th>
            <th>注册时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="members.length === 0">
            <td>暂无数据</td>
          </tr>
          <tr v-for="(member, key) in members" :key="key">
            <td>
              <a @click="redirectUserSet(member.uid)">{{member.uid}}</a>
            </td>
            <td>{{member.username}}</td>
            <td>{{member.email}}</td>
            <td>{{member.phone}}</td>
            <td>{{member.regdate | dateString}}</td>
            <td><a @click="unbind(key)">解绑</a></td>
          </tr>
        </tbody>
      </table>
    </div>
    <pagination :current-page.sync="page" :page-total="pageTotal" @change="loaddata"></pagination>
    <form ref="form" action="" method="post" style="display: none">
      <input type="text" name="uid" ref="uidInput">
      <input type="text" name="formhash" ref="formhash">
    </form>
  </div>
</template>

<script>
import axios from "@/lib/axios";
import dateString from "@/filters/date-string";
import select from "@/components/select";
import pagination from "@/components/pagination";

export default {
  filters: {
    dateString
  },
  data() {
    return {
      members: [],
      form: {
        key: "phone",
        value: ''
      },
      page: 1,
      pageTotal: 0
    };
  },
  created() {
    this.loaddata();
  },
  methods: {
    searchHandle() {
      this.page = 1;
      this.loaddata();
    },
    loaddata(page) {
      axios.get("/plugin.php?id=phone_auth&control=Members&action=find", {params: {
        ...this.form, page
      }})
        .then(({ data }) => {
          data = data.data;
          this.members = data.members;
          this.pageTotal = data.pageTotal
        });
    },
    unbind(key) {
      axios.post('/plugin.php?id=phone_auth&control=Members&action=unbind', {
        phone: this.members[key].phone
      })
      .then(({data}) => {
        data.status === 200 && this.members.splice(key, 1);
      })
    },
    redirectUserSet(uid) {
      let url = config.site_url + '/admin.php?action=members&operation=search';
      let form = this.$refs.form;
      form.setAttribute('action', url);
      this.$refs.uidInput.value = uid;
      this.$refs.formhash.value = config.formhash;
      form.submit();
    }
  },
  components: {
    ...select,
    pagination
  }
};
</script>
