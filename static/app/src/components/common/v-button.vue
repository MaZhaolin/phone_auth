<template>
  <button class="btn dz-btn" @click="clickHandle" :disabled="loading">
    <span v-if="loading">提交中</span>
    <slot v-else>提交</slot>
  </button>
</template>

<script>
export default {
  data() {
    return {
      loading: false
    }
  },
  methods: {
    clickHandle() {
      this.$emit('click-handle', this.toggleState);
    },
    toggleState() {
      this.loading = !this.loading;
    }
  }
}
</script>

