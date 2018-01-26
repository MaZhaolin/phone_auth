<template>
  <div>
    <div class="dz-item-group">
      <input class="form-control dz-input dz-input-icon" name="" type="text" placeholder="用户名3-5个字符不能纯数字" />
      <span class="form-icon">
        <v-img  src="/img/user-126x126.png"/>
      </span>
    </div>
    <div class="dz-item-group">
      <input class="form-control dz-input dz-input-icon" type="text" name="" placeholder="邮箱，用于接收系统通知" />
      <span class="form-icon">
        <v-img  src="/img/messege-126x126.png"/>
      </span>
    </div>
    <div class="dz-item-group dz-item-password">
      <input class="form-control dz-input dz-input-icon" name="" type="password" placeholder="密码6-20个字符" />
      <span class="form-icon">
        <v-img  src="/img/password-126x126.png"/>
      </span>
    </div>
    <div class="pw-strength">
      <div class="pw-bar"></div>
      <div class="pw-bar-on"></div>
    </div>
    <div class="dz-item-group">
      <input class="form-control dz-input dz-input-icon" type="text" name="phone" placeholder="手机号" />
      <span class="form-icon">
    						<v-img  src="/img/phone-126x126 .png"/>
    					</span>
    </div>
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
      <div class="vaptcha_container">
        <div class="vaptcha-init-main">
          <div class="vaptcha-init-loading">
            <a>
              <v-img src="https://cdn.vaptcha.com/vaptcha-loading.gif" />
            </a>
            <span class="vaptcha-text"><a href="https://www.vaptcha.com/" title="captcha" target="_blank">captcha </a>is initialing...</span>
          </div>
        </div>
      </div>
    </div>
    <div class="dz-item-group">
      <button class="btn dz-btn" id="register_btn" disabled="disabled">注册</button>
    </div>
    <div class="dz-item-group">
      <a class="dz-link-text dz-link" href="javascript:;">立即登录</a>
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>
