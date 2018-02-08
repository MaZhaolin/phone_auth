<template>
    <li @click="select">
        <a class="dropdown-item">
            <slot></slot>
        </a>
    </li>
</template>

<script>
export default {
    name: 'vp-option',
    props: ['value'],
    methods: {
        select() {
            this.$parent.$emit('change', this.value);
            // this.$parent.showOption = false;
        }
    }
}
</script>


