
const ctx = "@ClickOutSideContext";
const nodes = [];


document.addEventListener('click', (e) => {
    let elem = e.target;
    nodes.forEach(el => {
        if (!el.contains(elem)) {
            el[ctx].documentHandle();
        }
    })
})

export default {
    bind: (el, binding, vnode) => {
        el[ctx] = {
            id: nodes.push(el) - 1,
            documentHandle: binding.value
        }
    }
}