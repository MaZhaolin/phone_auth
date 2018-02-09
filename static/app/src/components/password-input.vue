<template>
  <div>
    <v-input v-model="password" class="dz-item-password" icon="password" type="password" placeholder="密码6-20个字符" />
    <div class="pw-strength" :class="[level]">
      <div class="pw-bar"></div>
      <div class="pw-bar-on"></div>
    </div>
  </div>
</template>

<script>
  export default {
    model: {
      prop: 'value',
      event: 'change'
    },
    data() {
      return {
        password: ''
      }
    },
    computed: {
      level() {
        var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
        var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        var enoughRegex = new RegExp("(?=.{6,}).*", "g");
        var allWordsRegex = new RegExp('[a-zA-Z]');
        if (this.password.length >= 6) {
          if (strongRegex.test(this.password)) {
            return 'pw-strong';
          } else if (mediumRegex.test(this.password) || allWordsRegex.test(this.password)) {
            return 'pw-medium'
          } else {
            return 'pw-weak';
          }
        } else if (this.password.length > 0 && this.password.length < 6) {
          return 'pw-weak';
        } else {
          return '';
        }
      }
    },
    watch: {
      password(v) {
        this.$emit('change', v);
      }
    }
  }
</script>

