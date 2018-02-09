<template>
  <div>
    <v-input v-model="form.username" icon="user" type="text" placeholder="用户名3-5个字符不能纯数字"/>
    <v-input v-model="form.email" icon="messege" type="text" placeholder="邮箱，用于接收系统通知"/>
    <PasswordInput v-model="form.password" ></PasswordInput>
    <v-input v-model="form.phone" :max-length="11" icon="phone" type="number" placeholder="手机号"/>
    <SendCodeInput v-model="form.code" :phone="form.phone"></SendCodeInput>
    <div class="dz-item-group">
      <v-button @click-handle="registerHandle">注册</v-button>
    </div>
    <div class="dz-item-group">
      <a class="dz-link-text dz-link" @click="$router.push({path: 'login'})">立即登录</a>
    </div>
  </div>
</template>

<script>
  import axios from "@/lib/axios";
  import PasswordInput from '@/components/password-input';
  import SendCodeInput from '@/components/send-code-input';

  export default {
    data() {
      return {
        form: {
          username: '',
          email: '',
          phone: '',
          code: '',
          password: ''
        }
      }
    },
    methods: {
      registerHandle(btn) {
        console.log(this.form.code);
        return;
        btn.disable('注册中');
        axios.post('/plugin.php?id=phone_auth&mod=logging&action=register', this.form)
        .then(({data}) => {
          console.log(data);
        })
        .catch(({response}) => {
          let data = response.data;
          this.$msg(data.msg);
          btn.enable();
        })
      }
    },
    components: {
      PasswordInput,
      SendCodeInput
    }
  }
</script>
