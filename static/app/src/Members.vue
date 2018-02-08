<template>
  <div class="user-management">
    <div class="management-title">
      &#29992;&#25143;&#31649;&#29702;
      <div class="right">
        <div class="search">
          <input type="text" class="input" @keydown.enter="searchHandle" v-model="form.value">
          <vp-select v-model="form.key">
            <vp-option value="phone">&#25163;&#26426;&#21495;</vp-option>
            <vp-option value="uid">ID</vp-option>
            <vp-option value="username">&#29992;&#25143;&#21517;</vp-option>
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
            <th>&#29992;&#25143;&#21517;</th>
            <th>&#37038;&#31665;</th>
            <th>&#25163;&#26426;&#21495;</th>
            <th>&#27880;&#20876;&#26102;&#38388;</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="members.length === 0">
            <td>&#26410;&#26597;&#35810;&#21040;&#25968;&#25454;</td>
          </tr>
          <tr v-for="(member, key) in members" :key="key">
            <td>
              <a @click="redirectUserSet(member.uid)">{{member.uid}}</a>
            </td>
            <td>{{member.username}}</td>
            <td>{{member.email}}</td>
            <td>{{member.phone}}</td>
            <td>{{member.regdate | dateString}}</td>
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
