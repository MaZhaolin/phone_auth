<template>
  <div class="dz-item-group">
    <input v-model="value" @focus="focusHandle" @blur="blurHandle" :class="klass" class="form-control dz-input dz-input-icon" :type="type" :placeholder="placeholder" />
    <span class="form-icon">
        <v-img :src="`/img/${icon}-126x126.png`"/>
    </span>
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
    }
  },
  watch: {
    value(v) {
      this.$emit('change', v);
    }
  }
}
</script>

