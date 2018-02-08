const formatValue =  (max) => {
    return function() {
        this.value = this.value.trim();
        if (this.value.replace(/[^\u0000-\u00ff]/g,"aa").length > max * 2) {
            let arr = this.value.split(''),
                length = 0;
            this.value = '';
            arr.forEach(c => {
                if (length >= max * 2) return;
                this.value += c;
                c.match(/[^\u0000-\u00ff]/g) ? length += 2 : length++
            })
        }
    }
}

export default {
    bind: (el, binding, vnode ) => {
        let formatValueCB = formatValue(binding.value);
        el.addEventListener('keyup', formatValueCB);
        el.addEventListener('compositionstart', function() {
            this.removeEventListener('keyup', formatValueCB);
        })
        el.addEventListener('compositionend', function() {
            el.addEventListener('keyup', formatValueCB);
            formatValueCB();
        })
    },
    // inserted: (el, binding) => {
        
    // }
}