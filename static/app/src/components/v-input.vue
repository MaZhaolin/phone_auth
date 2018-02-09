<template>
  <div class="dz-item-group">
    <input v-model="value" :maxlength="maxLength" @keydown="keydownHandle" @focus="focusHandle" @blur="blurHandle" :class="klass" class="form-control dz-input dz-input-icon" :type="type" :placeholder="placeholder" />
    <span class="form-icon iconfont" v-html="icon"></span>
  </div>
</template>

<script>
export default {
  name: 'v-input',
  props: {
    icon: {
      type: String
    },
    placeholder: {
      type: String
    },
    type: {
      type: String,
      default: 'text'
    },
    maxLength: {
      type: Number
    }
  },
  model: {
    prop: 'value',
    event: 'change'
  },
  data() {
    return {
      klass: [],
      value: ''
    }
  },
  methods: {
    focusHandle(e) {
      this.klass = [];
    },
    blurHandle() {
      this.$emit('validate', this);
      this.value.length === 0 && this.klass.push('error');
    },
    keydownHandle() {
      if (this.maxLength && this.value.length) {
        this.$nextTick(() => {
          this.value = this.value.substr(0, this.maxLength);
        })
      }
    }
  },
  watch: {
    value(v) {
      this.$emit('change', v);
    }
  }
}
</script>

