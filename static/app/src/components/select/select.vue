<template>
    <div class="btn-group dropdown" @click="toggleOption" v-click-out-side="() => showOption = false">
        <div class="dropdown-toggle-split dropdown-toggle">
            <button class="btn btn-default" :style="{width}">
                {{ options[selected] }}
            </button>
            <button type="button" class="btn btn-default">
                <i class="iconfont">&#xe615;</i>
            </button>
        </div>
        <ul class="dropdown-menu" v-show="showOption">
            <slot></slot>
        </ul>
    </div>
</template>

<script>
import ClickOutSide from '@/directives/click-out-side'

export default {
    model: {
        prop: 'selected',
        event: 'change',
    },
    props: {
        selected: {
            type: [Number, String],
            default: 0
        },
        width: {
            type: String,
            default: '60px'
        }
    },
    directives: { ClickOutSide },
    data() {
        return {
            showOption: false,
            options: []
        }
    },
    mounted() {
        let options = this.$children.filter(v => v.$options.name === 'vp-option')
        options.forEach(v => {
            this.options[v.value] = v.$slots.default[0].text
        })
        this.$forceUpdate()
    },
    methods: {
        toggleOption() {
            this.showOption = !this.showOption;
        }
    }
}
</script>
