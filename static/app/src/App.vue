<template>
  <div id="app">
    <div class="m-dz m-dz-login">
      <div class="m-dz-top">
				<span class="logo-img fl iconfont redirect-back " @click="$router.back()">&#xe7c2;</span>
        <span class="logo-text">{{title}}</span>
      </div>
      <div class="m-dz-main">
        <keep-alive>
          <router-view></router-view>
        </keep-alive>
        <div class="switch-cont">
          <div class="switch-way">
            <a class="way-text" href="">首页</a><span class="way-line"></span>
            <a class="way-text" @click="$router.push({path: '/login'})">登录</a><span class="way-line"></span>
            <a class="way-text" @click="$router.push({path: '/register'})">注册</a>
          </div>
          <div class="switch-way">
            <a class="way-text" href="">标准版</a><span class="way-line"></span>
            <a class="way-text" href="" disabled>触屏版</a><span class="way-line"></span>
            <a class="way-text" href="">电脑板</a><span class="way-line"></span>
            <a class="way-text" href="">客户端</a>
          </div>
          <div class="switch-way">
            <span class="way-text way-copy">© 2017 VAPTCHA All Rights Reserved </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'App',
    computed: {
      title() {
        switch(this.$route.name) {
          case 'login':
            return '登录';
          case 'register':
            return '注册';
        }
      }
    }
  }
</script>

<style>
  #app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
  }
</style>
