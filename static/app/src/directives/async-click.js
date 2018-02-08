const ctx = '@@asyncClickFunction';

export default {
    inserted(el, binding) {
        let status = binding.modifiers.status;
        let finish = (attachAgain = true) => {
            status && (el.innerText = el[ctx].content);
            attachAgain && el.addEventListener('click', el[ctx].event, {once: true});
        };
        let event = (...arg) => {
            status && (el.innerHTML = '<i class="vp-loading fa fa-spinner fa-pulse fa-3x fa-fw"></i> loading');
            binding.value && binding.value(finish, ...arg);
        };
        el[ctx] = {
            event,
            content: el.innerText
        }
        el.addEventListener('click', el[ctx].event, {once: true});
    }
}