<template>
  <img :src="static_src" alt="">
</template>

<script>
export default {
  props: ['src'],
  data() {
    return {
      static_src: config.static_path + this.src
    }
  }
}
</script>

