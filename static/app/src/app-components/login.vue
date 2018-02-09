<template>
  <div>
    <v-input v-model="form.user" :icon-class="[icon-phone]" icon="&#xecc8;" type="text" @validate="validator.user" placeholder="手机号/用户名"/>
    <v-input v-model="form.password" icon="&#xe6c5;" type="password" @validate="validator.password" placeholder="密码"/>
    <div class="dz-item-group">
      <vaptcha ref="vaptcha" scene="01" @success="captchaSuccess"></vaptcha>
    </div>
    <div class="dz-item-group">
      <v-button @click-handle="loginHandle">登录</v-button>
    </div>
    <div class="dz-item-group">
      <a class="dz-link fl" >忘记密码？</a>
      <a class="dz-link fr" @click="$router.push({path: '/register'})">立即注册</a>
    </div>
    <div class="dz-quick">
      <span class="dz-quick-line fl"></span>
      <span class="dz-quick-text">快捷登录</span>
      <span class="dz-quick-line fr"></span>
    </div>
    <div class="dz-qq-wechart">
      <div class="dz-qq-item">
        <span class="iconfont dz-qq">&#xe623;</span>
      </div>
      <div class="dz-wechart-item">
        <span class="iconfont dz-wechart">&#xe66a;</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "@/lib/axios";
import vaptchaVue from '@/components/vaptcha.vue';

export default {
  data() {
    return {
      form: {
        user: "",
        password: "",
        challenge: "",
        token: ""
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
  methods: {
    captchaSuccess(data) {
      this.form.vaptcha_challenge = data.challenge;
      this.form.vaptcha_token = data.token;
    },
    loginHandle(btn) {
      let vaptcha = this.$refs.vaptcha;
      if(!vaptcha.isValidated) {
        this.$msg('请进行人机验证');
        return ;
      }
      btn.disable('登录中');
      axios
        .post("/plugin.php?id=phone_auth&mod=logging&action=login&loginsubmit=yes", this.form)
        .then(({data}) => {
          console.log(data);
          if(typeof data === 'string') {
            this.$msg('系统错误');
          } else {
            location.href = config.site_url + '/forum.php?mobile=yes';
          }
          btn.enable();
       })
        .catch(({ response }) => {
          vaptcha.refresh();
          let data = response.data;
          this.$msg(data.msg);
          btn.enable();
        });
    }
  }
};
</script>

