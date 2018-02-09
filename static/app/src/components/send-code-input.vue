<template>
  <div>
    <div class="dz-item2-group clearfix dz-code-group" v-show="showInput">
      <input class="form-control dz-input dz-input-code dz-input-icon" name="code" placeholder="验证码" type="text" />
      <span class="form-icon">
        <v-img  src="/img/Verification-126x126.png"/>
      </span>
      <v-button class="dz-btn-code" ref="codeBtn" @click-handle="sendCode">发送验证码</v-button>
    </div>
    <div class="dz-item-group">
      <vaptcha ref="vaptcha" @success="vaptchaSuccess"/>
    </div>
  </div>
</template>

<script>
  import axios from "@/lib/axios";

  export default {
    props: ['phone'],
    model: {
      prop: 'value',
      event: 'change'
    },
    data() {
      return {
        value: '',
        vaptcha_token: '',
        showInput: true
      }
    },
    methods: {
      sendCode(btn) {
        if (!this.$refs.vaptcha.isValidated) {
          this.$msg('请进行人机验证');
          return ;
        }
        btn.disable('发送中');
        axios.post("/plugin.php?id=phone_auth&mod=logging&action=sendRegisterCode", {
          phone: this.phone,
          vaptcha_token: this.vaptcha_token
        })
        .then(({data}) => {
          this.countDown(btn);
        })
        .catch(({response}) => {
          let data = response.data;
          if(data.status === 301) {
            this.countDown(btn, data.msg);
            return ;
          }
          this.$msg(data.msg);
          btn.enable();
        })
      },
      vaptchaSuccess(data) {
        this.vaptcha_token = data.token;
        this.showInput = true;
        this.$refs.codeBtn.clickHandle();
      },
      countDown(btn, time) {
        time = time || 120;
        btn.disable(time + 's');
        (function cd(){
          let countDownTimer = setTimeout(() => {
            if (time === 1){
              btn.enable();
              clearTimeout(countDownTimer);
              return ;
            }
            time --;
            btn.disable(time + 's');
            cd();
          }, 1000);
        })()
      }
    },
    watch: {
      value(v) {
        this.$emit('change', v);
      }
    }
  }
</script>

