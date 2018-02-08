<template>
  <div>
    <v-input v-model="form.user" icon="user" type="text" @validate="validator.user" placeholder="手机号/用户名"/>
    <v-input v-model="form.password" icon="password" type="password" @validate="validator.password" placeholder="密码"/>
    <div class="dz-item-group">
      <vaptcha ref="vaptcha" @success="captchaSuccess"></vaptcha>
    </div>
    <div class="dz-item-group">
      <v-button @click-handle="loginHandle">登录</v-button>
    </div>
    <div class="dz-item-group">
      <a class="dz-link fl" >忘记密码？</a>
      <a class="dz-link fr" @click="$router.push({path: '/register'})">立即注册</a>
    </div>
  </div>
</template>

<script>
import axios from "@/lib/axios";
import vaptchaVue from './common/vaptcha.vue';

export default {
  data() {
    return {
      form: {
        user: "",
        password: ""
      },
      validator: {
        user: ({value, klass}) => {
          value = value.trim();
          if (0 === value.length) {
            klass.push('error');
          }
        },
        password: ({value, klass}) => {
          if (value.length < 6) {
            klass.push('error');
          }
        }
      }
    };
  },
  created() {
    console.log(this);
  },
  methods: {
    captchaSuccess(data) {
      this.form = { ...this.form, ...data };
    },
    loginHandle(toggleState) {
      let vaptcha = this.$refs.vaptcha;
      if(!vaptcha.isValidated) {
        this.$msg('请进行人机验证');
        return ;
      }
      toggleState();
      axios
        .post("/plugin.php?id=phone_auth&mod=logging&action=login&loginsubmit=yes", this.form)
        .then(({data}) => {
          console.log(data);
          if(typeof data === 'string') {
            this.$msg('系统错误');
          } else {
            location.href = config.site_url + '/forum.php?mobile=yes';
          }
          toggleState();
       })
        .catch(({ response }) => {
          vaptcha.refresh();
          let data = response.data;
          this.$msg(data.msg);
          toggleState();
        });
    }
  }
};
</script>

