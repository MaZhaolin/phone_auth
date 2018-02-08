<template>
  <div>
    <v-input icon="user" type="text" placeholder="用户名3-5个字符不能纯数字"/>
    <v-input icon="messege" type="text" placeholder="邮箱，用于接收系统通知"/>
    <v-input class="dz-item-password" icon="password" type="password" placeholder="密码6-20个字符"/>
    <div class="pw-strength">
      <div class="pw-bar"></div>
      <div class="pw-bar-on"></div>
    </div>
    <v-input icon="messege" type="text" placeholder="邮箱，用于接收系统通知"/>
    <v-input icon="phone" type="text" placeholder="手机号"/>
    <div class="dz-item2-group clearfix dz-code-group none">
      <input class="form-control dz-input dz-input-code dz-input-icon" name="code" placeholder="验证码" type="text" />
      <span class="form-icon">
        <v-img  src="/img/Verification-126x126.png"/>
      </span>
      <button class="btn dz-btn dz-btn-code">发送验证码</button>
    </div>
    <div class="dz-item-group">
      <input type="hidden" name="vaptcha_token">
      <input type="hidden" name="vaptcha_challenge">
      <vaptcha/>
    </div>
    <div class="dz-item-group">
      <button class="btn dz-btn" disabled="disabled">注册</button>
    </div>
    <div class="dz-item-group">
      <a class="dz-link-text dz-link" @click="$router.push({path: 'login'})">立即登录</a>
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>
