const toBool = v => {
    if (v === false || v === 'false'){
        return false;
    } else {
        return true;
    }
};

const updateIcon = (el, isAscend) => {
    if(!el) return ;
    let classList = el.classList;
    classList.remove('active');
    el.dataset.isAscend = isAscend;
    if (isAscend) {
        classList.add('fa-arrow-up');
        classList.remove('fa-arrow-down')
    } else {
        classList.remove('fa-arrow-up');
        classList.add('fa-arrow-down')
    }
 };

export default {
    bind: (el, binding, vnode ) => {
        el.dataset.isAscend = binding.modifiers.isAscend || 'false';
        el.classList.add('sort');
        if (binding.modifiers.default) {
            el.classList.add('active');
        }
    },
    inserted: (el, binding) => {
        el.addEventListener('click', () => {
            let isAscend = toBool(el.dataset.isAscend),
                classList = el.classList;
            if (classList.contains('active')) {
                isAscend = !isAscend;
                el.dataset.isAscend = isAscend;
                updateIcon(el, isAscend)
            } else {
                updateIcon(document.querySelector('.sort.active'), false)
            }
            classList.add('active');
            binding.value({
                sort: binding.arg,
                isascend: toBool(isAscend)
            });
        })
    }
}