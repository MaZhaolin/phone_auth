<template>
  <button class="btn dz-btn" @click="clickHandle" :disabled="disabled">
    <span v-if="disabled">{{disabledText}}</span>
    <slot v-else>提交</slot>
  </button>
</template>

<script>
export default {
  data() {
    return {
      disabled: false,
      disabledText: '提交中'
    }
  },
  methods: {
    clickHandle() {
      this.$emit('click-handle', {
        disable: this.disable,
        enable: this.enable
      });
    },
    disable(text) {
      this.disabled = true;
      text && (this.disabledText = text);
    },
    enable() {
      this.disabled = false;
    }
  }
}
</script>

