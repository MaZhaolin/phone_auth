<template>
  <div ref="vaptcha" class="vaptcha_container">
   <div class="vaptcha-init-main">
      <div class="vaptcha-init-loading">
        <a href="https://www.vaptcha.com/" target="_blank"><img src="https://cdn.vaptcha.com/vaptcha-loading.gif"/></a>
        <span class="vaptcha-text">VAPTCHA启动中...</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '@/lib/axios';

export default {
  props: {
    scene: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      vaptcha: null,
      isValidated: false
     }
  },
  created() {
    this.init();
  },
  methods: {
    init() {
      let script = document.getElementById('vaptcha_sdk');
      if(script) {
        this.initVaptcha();
        return ;
      }
      script = document.createElement('script');
      script.id = 'vaptcha_sdk';
      script.src = 'https://cdn.vaptcha.com/v.js';
      script.onload = this.initVaptcha;
      document.body.append(script);
    },
    initVaptcha() {
      axios.get('/plugin.php?id=phone_auth&action=getchallenge', {
        params: {scene: this.scene}
      })
      .then(({data}) => {
        let config={
          vid: data.vid, //验证单元id, string, 必填
          challenge: data.challenge, //验证流水号, string, 必填
          container: this.$refs.vaptcha,//验证码容器, HTMLElement或者selector, 必填
          type: "popup", //必填，表示点击式验证模式,
          style: window.config.vaptcha.style,
          color: window.config.vaptcha.color,
          // ai: false,
          outage: "http://localhost:4000/api/vaptcha/downtime", //服务器端配置的宕机模式接口地址
          success: (token,challenge) => {//验证成功回调函数, 参数token, challenge 为string, 必填
            this.isValidated = true;
            this.$emit('success', {token, challenge});
          },
          fail:() => {//验证失败回调函数
              //todo:执行人机验证失败后的操作
          }
        };
        vaptcha(config, obj => {
          this.vaptcha = obj;
          obj.init();
        })
      })
      .catch(err => {

      })
    },
    refresh() {
      if (!this.isValidated) return ;
      this.isValidated = false;
      this.vaptcha.destroy();
      this.initVaptcha();
    }
  }
}
</script>

<style>
  .vaptcha-init-main {
    display: table;
    width: 100%;
    height: 100%;
    background-color: #EEEEEE;
  }
  .vaptcha-init-loading {
    display: table-cell;
    vertical-align: middle;
    text-align: center
  }
  .vaptcha-init-loading>a {
    display: inline-block;
    width: 18px;
    height: 18px;
  }
  .vaptcha-init-loading>a img {
    vertical-align: middle
  }
  .vaptcha-init-loading .vaptcha-text {
    font-family: sans-serif;
    font-size: 12px;
    color: #CCCCCC;
    vertical-align: middle
  }
</style>
