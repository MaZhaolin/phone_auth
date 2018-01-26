<template>
  <div>
    <div class="dz-item-group">
        <input v-model="form.user" class="form-control dz-input dz-input-icon" name="user" type="text" placeholder="用户名/手机号" />
        <span class="form-icon">
            <v-img src="/img/user-126x126.png"/>
          </span>
      </div>
      <div class="dz-item-group">
        <input v-model="form.password" class="form-control dz-input dz-input-icon" name="password" type="password" placeholder="密码" />
        <span class="form-icon">
          <v-img src="/img/password-126x126.png"/>
        </span>
      </div>
      <div class="dz-item-group">
        <vaptcha></vaptcha>
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
  import vaptcha from './common/vaptcha';
  import axios from '@/lib/axios';

  export default {
    data() {
      return {
        form: {
          user: '',
          password: ''
        }
      };
    },
    created() {

    },
    methods: {
      loginHandle(toggleState) {
        toggleState();
        axios.post('login')
        .then(r => {
          console.log(r);
          toggleState();
        })
        .catch(({response}) => {
          data
        })
      }
    },
    components: {
      vaptcha
    }
  }
</script>

