<template>
  <div class="dz-m-tip" :class="{'dz-m-tip-success': type === 'success'}">
    <div class="dz-m-cont">
      <span class="dz-tip-icon"></span>
      <span class="dz-tip-text">{{msg}}</span>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        type: 'error',
        msg: ''
      };
    }
  };
</script>
